"use client";

import { AlertCircle, ArrowRight, Keyboard, Loader2, Mic, Sparkles, Square } from "lucide-react";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { Button, Card } from "@/components/ui";
import type { AssistantAction } from "@/lib/assistant/resolve";
import { useDictation } from "@/lib/use-dictation";
import { cn } from "@/lib/utils";
import { ClientReview, OrderReview } from "./assistant-forms";

type Phase = "idle" | "recording" | "stopping" | "thinking" | "done";

const examples = [
  "Nouvelle cliente Awa Sy, 76 12 34 56, boubou en bazin pour samedi, 45 000, acompte 20 000 en Wave.",
  "Aminata a payé 15 000 en Orange Money.",
  "La robe de Mariama est prête.",
  "Tour de taille 78 et poitrine 96 pour Khady.",
  "Montre-moi la caisse.",
];

function localToday() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

export function Assistant() {
  const router = useRouter();
  const [phase, setPhase] = useState<Phase>("idle");
  const [typed, setTyped] = useState(false);
  const [text, setText] = useState("");
  const [sent, setSent] = useState("");
  const [action, setAction] = useState<AssistantAction | null>(null);
  const [source, setSource] = useState<"llm" | "local" | null>(null);
  const [failure, setFailure] = useState<string | null>(null);
  const [seconds, setSeconds] = useState(0);

  // chronomètre pendant l'écoute
  useEffect(() => {
    if (phase !== "recording") return;
    const t = setInterval(() => setSeconds((s) => s + 1), 1000);
    return () => clearInterval(t);
  }, [phase]);

  // la reconnaissance s'est arrêtée (bouton stop, silence, erreur) : on envoie ce qui a été compris
  const dictation = useDictation({
    onEnd: (said, error) => {
      if (said) void understand(said);
      else {
        setPhase("idle");
        if (!error) setFailure("Je n'ai rien entendu. Rapprochez-vous du micro et réessayez.");
      }
    },
  });

  async function understand(transcript: string) {
    setSent(transcript);
    setPhase("thinking");
    setFailure(null);
    setAction(null);
    try {
      const res = await fetch("/api/assistant", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transcript, today: localToday() }),
        signal: AbortSignal.timeout(25_000),
      });
      if (!res.ok) throw new Error(String(res.status));
      const data = (await res.json()) as { action: AssistantAction; source: "llm" | "local" };
      setAction(data.action);
      setSource(data.source);
      setPhase("done");
      setText("");
      if (data.action.kind === "navigate") router.push(data.action.href);
    } catch {
      setFailure("Impossible de joindre le serveur. Vérifiez la connexion et réessayez.");
      setPhase("idle");
    }
  }

  function startRecording() {
    setFailure(null);
    setAction(null);
    setSeconds(0);
    dictation.start();
    setPhase("recording");
  }

  function reset() {
    setPhase("idle");
    setText("");
    setSent("");
    setAction(null);
    setFailure(null);
  }

  /* ───────── Résultat : formulaire pré-rempli ou redirection ───────── */
  if (phase === "done" && action && action.kind !== "reply") {
    return (
      <div className="mx-auto max-w-xl space-y-4">
        <Card className="p-4">
          <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-muted">
            <Mic className="size-3.5" /> Vous avez dit
          </p>
          <p className="mt-2 font-display text-lg leading-snug text-ink">« {sent} »</p>
          <AssistantBubble text={action.reply} local={source === "local"} />
        </Card>

        {action.kind === "order_form" && <OrderReview draft={action.draft} clientId={action.clientId} onReset={reset} />}
        {action.kind === "client_form" && (
          <ClientReview mode={action.mode} clientId={action.clientId} client={action.client} measurements={action.measurements} onReset={reset} />
        )}
        {action.kind === "navigate" && (
          <Card className="flex items-center gap-3 p-4">
            <Loader2 className="size-5 animate-spin text-gold" />
            <p className="flex-1 font-semibold text-ink">Ouverture de {action.label}…</p>
            <Button size="sm" variant="outline" onClick={() => router.push(action.href)}>
              Ouvrir <ArrowRight className="size-4" />
            </Button>
          </Card>
        )}
      </div>
    );
  }

  /* ───────── Écoute / saisie ───────── */
  const recording = phase === "recording" || phase === "stopping";
  const busy = phase === "thinking" || phase === "stopping";
  const unsupported = dictation.supported === false;
  const showTyping = typed || unsupported;
  const message = failure ?? dictation.error;

  return (
    <div className="mx-auto flex min-h-[calc(100dvh-14rem)] max-w-xl flex-col items-center justify-center text-center">
      {message && (
        <p role="alert" className="mb-6 flex w-full items-start gap-2 rounded-2xl bg-clay-soft px-4 py-3 text-left text-sm font-semibold text-clay">
          <AlertCircle className="mt-0.5 size-4 shrink-0" /> {message}
        </p>
      )}

      {/* réponse simple de l'assistant (salutation, question…) */}
      {phase === "done" && action?.kind === "reply" && (
        <Card className="mb-8 w-full p-4 text-left">
          <p className="font-display text-lg leading-snug text-muted">« {sent} »</p>
          <AssistantBubble text={action.reply} local={source === "local"} />
        </Card>
      )}

      {showTyping ? (
        <form
          className="w-full space-y-3"
          onSubmit={(e) => {
            e.preventDefault();
            if (text.trim()) void understand(text.trim());
          }}
        >
          {unsupported && (
            <p className="text-sm text-muted">La dictée n&apos;est pas disponible sur ce navigateur (utilisez Chrome, Edge ou Safari). Écrivez votre demande :</p>
          )}
          <textarea
            value={text}
            onChange={(e) => setText(e.target.value)}
            rows={4}
            autoFocus
            placeholder={examples[0]}
            className="w-full rounded-3xl border border-line bg-surface p-5 font-display text-xl leading-snug text-ink outline-none placeholder:text-muted/60 focus:border-gold focus:ring-4 focus:ring-gold/20"
          />
          <Button type="submit" size="lg" className="w-full" disabled={busy || !text.trim()}>
            {phase === "thinking" ? <Loader2 className="size-4 animate-spin" /> : <Sparkles className="size-4" />}
            {phase === "thinking" ? "Je réfléchis…" : "Envoyer"}
          </Button>
          {!unsupported && (
            <button type="button" onClick={() => setTyped(false)} className="text-sm font-semibold text-muted hover:text-ink">
              Revenir au micro
            </button>
          )}
        </form>
      ) : (
        <>
          <p className="font-display text-3xl font-medium leading-tight text-ink sm:text-4xl">
            {recording ? (
              "Je vous écoute…"
            ) : phase === "thinking" ? (
              "Je réfléchis…"
            ) : (
              <>
                Que voulez-vous faire ?
                <br />
                <span className="italic text-clay">Dites-le simplement.</span>
              </>
            )}
          </p>
          <p className="mt-3 max-w-sm text-ink-soft">
            {recording
              ? `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, "0")} · touchez le carré quand vous avez fini`
              : phase === "thinking"
                ? `« ${sent} »`
                : "Commande, client, mesures, paiement, avancement… je m'occupe d'ouvrir le bon écran."}
          </p>

          <div className="relative my-10 grid place-items-center">
            {recording && (
              <span
                className="absolute size-36 rounded-full bg-clay/25 transition-transform duration-100"
                style={{ transform: `scale(${1 + Math.max(...dictation.levels) * 0.6})` }}
              />
            )}
            <button
              type="button"
              onClick={() => {
                if (recording) {
                  setPhase("stopping");
                  dictation.stop();
                } else startRecording();
              }}
              disabled={busy || dictation.supported === null}
              className={cn(
                "relative grid size-36 place-items-center rounded-full shadow-2xl transition-all duration-300 active:scale-95 disabled:opacity-90",
                recording ? "bg-clay text-white" : "bg-gold text-gold-ink shadow-[0_20px_60px_-15px_var(--gold)]",
                phase === "thinking" && "bg-night text-gold",
              )}
              aria-label={recording ? "Arrêter et envoyer" : "Commencer la dictée"}
            >
              {busy ? <Loader2 className="size-12 animate-spin" /> : recording ? <Square className="size-10 fill-current" /> : <Mic className="size-14" />}
            </button>
          </div>

          {recording ? (
            <>
              <div className="flex h-12 w-full max-w-xs items-center gap-1" aria-hidden>
                {dictation.levels.map((l, i) => (
                  <span key={i} className="flex-1 rounded-full bg-clay/80 transition-[height] duration-75" style={{ height: `${Math.max(8, l * 100)}%` }} />
                ))}
              </div>
              <p className="mt-6 min-h-[4.5rem] w-full rounded-3xl bg-surface p-4 text-left font-display text-lg leading-snug text-ink ring-1 ring-line" aria-live="polite">
                {dictation.finalText}
                {dictation.interim && <span className="text-muted"> {dictation.interim}</span>}
                {!dictation.transcript && <span className="text-muted">La transcription s&apos;affiche ici pendant que vous parlez…</span>}
              </p>
            </>
          ) : (
            (phase === "idle" || phase === "done") && (
              <>
                <button type="button" onClick={() => setTyped(true)} className="flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-muted hover:bg-surface-2 hover:text-ink">
                  <Keyboard className="size-4" /> Écrire plutôt
                </button>
                <div className="mt-6 flex flex-wrap justify-center gap-2">
                  {examples.slice(1).map((ex) => (
                    <button
                      key={ex}
                      type="button"
                      onClick={() => void understand(ex)}
                      className="rounded-full border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink-soft transition hover:border-gold hover:text-ink"
                    >
                      {ex}
                    </button>
                  ))}
                </div>
              </>
            )
          )}
        </>
      )}
    </div>
  );
}

function AssistantBubble({ text, local }: { text: string; local: boolean }) {
  if (!text) return null;
  return (
    <div className="mt-3 flex items-start gap-2.5 rounded-2xl bg-gold-soft/50 p-3">
      <span className="grid size-7 shrink-0 place-items-center rounded-full bg-gold text-gold-ink">
        <Sparkles className="size-3.5" />
      </span>
      <div>
        <p className="text-sm font-semibold leading-snug text-ink">{text}</p>
        {local && <p className="mt-0.5 text-[0.7rem] font-semibold text-muted">Analyse simple — ajoutez GROQ_API_KEY pour l&apos;assistant IA</p>}
      </div>
    </div>
  );
}
