import Anthropic from "@anthropic-ai/sdk";
import { betaZodOutputFormat } from "@anthropic-ai/sdk/helpers/beta/zod";
import { OrderDraftSchema, parseDraftLocally, type OrderDraft } from "@/lib/order-draft";

const SYSTEM = `Tu es l'assistant d'un atelier de couture en Afrique de l'Ouest.
On te donne la transcription d'une note vocale dictée par le tailleur. Extrais la commande.

Règles :
- N'invente rien : un champ non mentionné vaut null.
- La transcription vient d'une reconnaissance vocale : corrige les erreurs évidentes (« bas un » → bazin, « vague » → Wave, « orange mané » → Orange Money).
- Le français peut être mêlé de wolof, bambara ou dioula.
- Montants en francs CFA, en entiers (« quarante-cinq mille » → 45000, « 45 » dans un contexte de prix → 45000).
- Dates relatives (« samedi », « dans une semaine ») : convertis-les au format AAAA-MM-JJ à partir de la date du jour fournie.
- Nom du client avec majuscules ; tenue et tissu en français courant.`;

let client: Anthropic | null = null;

export async function POST(request: Request) {
  const body = (await request.json().catch(() => null)) as { transcript?: unknown; today?: unknown } | null;
  const transcript = typeof body?.transcript === "string" ? body.transcript.trim().slice(0, 4000) : "";
  if (!transcript) return Response.json({ error: "Transcription vide." }, { status: 400 });

  const today = typeof body?.today === "string" && /^\d{4}-\d{2}-\d{2}$/.test(body.today) ? body.today : new Date().toISOString().slice(0, 10);
  const local = () => parseDraftLocally(transcript, new Date(`${today}T12:00:00`));

  if (!process.env.ANTHROPIC_API_KEY && !process.env.ANTHROPIC_AUTH_TOKEN) {
    return Response.json({ draft: local(), source: "local" });
  }

  try {
    client ??= new Anthropic();
    const weekday = new Date(`${today}T12:00:00`).toLocaleDateString("fr-FR", { weekday: "long" });
    const response = await client.beta.messages.parse({
      model: "claude-opus-5-5",
      max_tokens: 4000,
      betas: ["server-side-fallback-2026-07-01"],
      fallbacks: "default",
      output_config: { effort: "low", format: betaZodOutputFormat(OrderDraftSchema) },
      system: SYSTEM,
      messages: [{ role: "user", content: `Date du jour : ${weekday} ${today}\n\nTranscription :\n${transcript}` }],
    });

    const draft: OrderDraft | null = response.stop_reason === "refusal" ? null : response.parsed_output;
    if (!draft) return Response.json({ draft: local(), source: "local" });
    return Response.json({ draft, source: "claude" });
  } catch (error) {
    if (error instanceof Anthropic.AuthenticationError) console.error("[assistant] clé API invalide");
    else if (error instanceof Anthropic.RateLimitError) console.error("[assistant] limite de requêtes atteinte");
    else if (error instanceof Anthropic.APIError) console.error(`[assistant] erreur API ${error.status}: ${error.message}`);
    else console.error("[assistant]", error);
    return Response.json({ draft: local(), source: "local" });
  }
}
