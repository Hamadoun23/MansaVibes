import Groq from "groq-sdk";
import { emptyIntent, intentJsonSchema, IntentSchema, SYSTEM_PROMPT, type Intent } from "@/lib/assistant/intent";
import { resolveIntent } from "@/lib/assistant/resolve";
import { parseDraftLocally } from "@/lib/order-draft";

const MODEL = process.env.GROQ_MODEL ?? "openai/gpt-oss-120b";

let groq: Groq | null = null;

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
    groq ??= new Groq();
    const weekday = new Date(`${today}T12:00:00`).toLocaleDateString("fr-FR", { weekday: "long" });
    const completion = await groq.chat.completions.create({
      model: MODEL,
      temperature: 0.2,
      reasoning_effort: "low",
      messages: [
        { role: "system", content: SYSTEM_PROMPT },
        { role: "user", content: `Date du jour : ${weekday} ${today}\n\nNote du tailleur :\n${transcript}` },
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
    return Response.json({ action: resolveIntent(parsed.data), source: "llm" });
  } catch (error) {
    if (error instanceof Groq.AuthenticationError) console.error("[assistant] clé GROQ_API_KEY invalide");
    else if (error instanceof Groq.RateLimitError) console.error("[assistant] quota Groq atteint");
    else if (error instanceof Groq.APIError) console.error(`[assistant] erreur Groq ${error.status}: ${error.message}`);
    else console.error("[assistant]", error);
    return Response.json({ action: resolveIntent(localIntent(transcript, today)), source: "local" });
  }
}
