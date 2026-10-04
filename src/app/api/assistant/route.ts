import Groq from "groq-sdk";
import { emptyIntent, intentJsonSchema, IntentSchema, SYSTEM_PROMPT, type Intent } from "@/lib/assistant/intent";
import { resolveIntent } from "@/lib/assistant/resolve";
import { parseDraftLocally } from "@/lib/order-draft";

const MODEL = process.env.GROQ_MODEL ?? "openai/gpt-oss-120b";

let groq: Groq | null = null;

/** Les 15 prochains jours avec leur nom : le modèle lit la date au lieu de la calculer (il se trompe souvent). */
function buildCalendar(today: string) {
  const start = new Date(`${today}T12:00:00`);
  return Array.from({ length: 15 }, (_, i) => {
    const d = new Date(start.getTime() + i * 864e5);
    const iso = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    const name = d.toLocaleDateString("fr-FR", { weekday: "long", day: "numeric", month: "long" });
    const tag = i === 0 ? " (aujourd'hui)" : i === 1 ? " (demain)" : i === 2 ? " (après-demain)" : i === 7 ? " (dans une semaine)" : "";
    return `${iso} = ${name}${tag}`;
  }).join("\n");
}

/** Sans LLM : on suppose une commande et on applique l'analyse par mots-clés. */
function localIntent(transcript: string, today: string): Intent {
  const d = parseDraftLocally(transcript, new Date(`${today}T12:00:00`));
  const looksLikeOrder = Boolean(d.garment || d.price || d.fabric);
  return {
    ...emptyIntent,
    intent: looksLikeOrder ? "create_order" : "chat",
    reply: looksLikeOrder
      ? "Analyse simple (assistant IA non configuré) : vérifiez bien les champs."
      : "L'assistant IA n'est pas configuré : je ne comprends que les commandes simples pour l'instant.",
    ...d,
  };
}

export async function POST(request: Request) {
  const body = (await request.json().catch(() => null)) as { transcript?: unknown; today?: unknown } | null;
  const transcript = typeof body?.transcript === "string" ? body.transcript.trim().slice(0, 4000) : "";
  if (!transcript) return Response.json({ error: "Transcription vide." }, { status: 400 });
  const today = typeof body?.today === "string" && /^\d{4}-\d{2}-\d{2}$/.test(body.today) ? body.today : new Date().toISOString().slice(0, 10);

  if (!process.env.GROQ_API_KEY) {
    return Response.json({ action: resolveIntent(localIntent(transcript, today)), source: "local" });
  }

  try {
    groq ??= new Groq({ timeout: 15_000, maxRetries: 1 });
    const calendar = buildCalendar(today);
    const completion = await groq.chat.completions.create({
      model: MODEL,
      temperature: 0.2,
      reasoning_effort: "low",
      messages: [
        { role: "system", content: SYSTEM_PROMPT },
        { role: "user", content: `Calendrier :\n${calendar}\n\nNote du tailleur :\n${transcript}` },
      ],
      response_format: {
        type: "json_schema",
        json_schema: { name: "assistant_intent", strict: true, schema: intentJsonSchema },
      },
    });

    const raw = completion.choices[0]?.message?.content;
    const parsed = raw ? IntentSchema.safeParse(JSON.parse(raw)) : null;
    if (!parsed?.success) {
      console.error("[assistant] réponse invalide du modèle", raw);
      return Response.json({ action: resolveIntent(localIntent(transcript, today)), source: "local" });
    }
    // filets de sécurité : la date d'un jour cité (« vendredi », « demain ») est calculée ici, le modèle se trompe
    // souvent d'un jour ; moyen de paiement et téléphone cités mais oubliés sont repris de la phrase.
    const heard = parseDraftLocally(transcript, new Date(`${today}T12:00:00`));
    const intent: Intent = {
      ...parsed.data,
      due_date: heard.due_date ?? parsed.data.due_date,
      payment_method: parsed.data.payment_method ?? heard.payment_method,
      phone: parsed.data.phone ?? heard.phone,
    };
    return Response.json({ action: resolveIntent(intent, transcript), source: "llm" });
  } catch (error) {
    if (error instanceof Groq.AuthenticationError) console.error("[assistant] clé GROQ_API_KEY invalide");
    else if (error instanceof Groq.RateLimitError) console.error("[assistant] quota Groq atteint");
    else if (error instanceof Groq.APIError) console.error(`[assistant] erreur Groq ${error.status}: ${error.message}`);
    else console.error("[assistant]", error);
    return Response.json({ action: resolveIntent(localIntent(transcript, today)), source: "local" });
  }
}
