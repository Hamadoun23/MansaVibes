"use client";

import { Check, Keyboard, Loader2, Mic, RotateCcw, Sparkles, Square } from "lucide-react";
import Link from "next/link";
import { useEffect, useState } from "react";
import { Button, Card } from "@/components/ui";
import { cn, fcfa } from "@/lib/utils";

type Phase = "idle" | "recording" | "thinking" | "review" | "saved";

const sample = "Nouvelle cliente Awa Sy, 76 12 34 56. Boubou en bazin bleu nuit pour samedi, 45 000. Elle a donné 20 000 en Wave.";

const initialDraft = {
  client: "Awa Sy",
  phone: "76 12 34 56",
  garment: "Boubou",
  fabric: "Bazin bleu nuit",
  due: "Samedi 10 octobre",
  price: "45000",
  deposit: "20000",
  method: "Wave",
};

/**
 * Démo de l'assistant : l'enregistrement est simulé, la compréhension renvoie un brouillon fixe.
 * Le branchement réel (transcription + Claude) passera par une route API.
 */
export function Assistant() {
  const [phase, setPhase] = useState<Phase>("idle");
  const [seconds, setSeconds] = useState(0);
  const [typed, setTyped] = useState(false);
  const [text, setText] = useState("");
  const [draft, setDraft] = useState(initialDraft);

  useEffect(() => {
    if (phase !== "recording") return;
    const t = setInterval(() => setSeconds((s) => s + 1), 1000);
    return () => clearInterval(t);
  }, [phase]);

  useEffect(() => {
    if (phase !== "thinking") return;
    const t = setTimeout(() => setPhase("review"), 1600);
    return () => clearTimeout(t);
  }, [phase]);

  const reset = () => {
    setPhase("idle");
    setSeconds(0);
    setText("");
    setDraft(initialDraft);
  };

  const rest = Math.max(0, Number(draft.price) - Number(draft.deposit));

  if (phase === "review" || phase === "saved") {
    return (
      <div className="mx-auto max-w-xl space-y-4">
        <Card className="p-4">
          <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-muted">
            <Mic className="size-3.5" /> Vous avez dit
          </p>
          <p className="mt-2 font-display text-lg leading-snug text-ink">« {text || sample} »</p>
        </Card>

        <Card className="overflow-hidden">
          <div className="flex items-center justify-between border-b border-line bg-gold-soft/40 px-5 py-3.5">
            <p className="flex items-center gap-2 font-bold text-ink">
              <Sparkles className="size-4 text-gold" /> Nouvelle commande
            </p>
            <span className="text-xs font-semibold text-muted">Vérifiez puis validez</span>
          </div>
          <div className="grid gap-x-4 gap-y-3 p-5 sm:grid-cols-2">
            <DraftField label="Cliente" value={draft.client} onChange={(v) => setDraft({ ...draft, client: v })} badge="nouvelle fiche" />
            <DraftField label="Téléphone" value={draft.phone} onChange={(v) => setDraft({ ...draft, phone: v })} inputMode="tel" />
            <DraftField label="Tenue" value={draft.garment} onChange={(v) => setDraft({ ...draft, garment: v })} />
            <DraftField label="Tissu" value={draft.fabric} onChange={(v) => setDraft({ ...draft, fabric: v })} />
            <DraftField label="Livraison" value={draft.due} onChange={(v) => setDraft({ ...draft, due: v })} />
            <DraftField label="Prix (F)" value={draft.price} onChange={(v) => setDraft({ ...draft, price: v.replace(/\D/g, "") })} inputMode="numeric" />
            <DraftField label="Acompte (F)" value={draft.deposit} onChange={(v) => setDraft({ ...draft, deposit: v.replace(/\D/g, "") })} inputMode="numeric" />
            <DraftField label="Payé par" value={draft.method} onChange={(v) => setDraft({ ...draft, method: v })} />
          </div>
          <div className="flex items-center justify-between border-t border-line px-5 py-3.5 text-sm">
            <span className="font-semibold text-muted">Reste à payer</span>
            <span className="font-display text-xl font-semibold text-ink">{fcfa(rest)}</span>
          </div>
        </Card>

        {phase === "saved" ? (
          <div className="rounded-3xl bg-leaf p-5 text-white">
            <p className="flex items-center gap-2 font-display text-xl font-semibold">
              <Check className="size-5" /> Commande enregistrée
            </p>
            <p className="mt-1 text-sm text-white/80">Fiche cliente créée, acompte encaissé. Le reçu WhatsApp est prêt.</p>
            <div className="mt-4 grid grid-cols-2 gap-2">
              <Link href="/app/commandes" className="flex h-11 items-center justify-center rounded-full bg-white/15 text-sm font-bold hover:bg-white/25">
                Voir les commandes
              </Link>
              <button type="button" onClick={reset} className="flex h-11 items-center justify-center gap-2 rounded-full bg-white text-sm font-bold text-leaf">
                <Mic className="size-4" /> Une autre
              </button>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-[auto_1fr] gap-2">
            <Button variant="outline" size="lg" onClick={reset} aria-label="Recommencer">
              <RotateCcw className="size-4" />
            </Button>
            <Button size="lg" onClick={() => setPhase("saved")}>
              <Check className="size-4" /> Valider et enregistrer
            </Button>
          </div>
        )}
      </div>
    );
  }

  return (
    <div className="mx-auto flex min-h-[calc(100dvh-14rem)] max-w-xl flex-col items-center justify-center text-center">
      {typed ? (
        <form
          className="w-full space-y-3"
          onSubmit={(e) => {
            e.preventDefault();
            setPhase("thinking");
          }}
        >
          <textarea
            value={text}
            onChange={(e) => setText(e.target.value)}
            rows={5}
            autoFocus
            placeholder={sample}
            className="w-full rounded-3xl border border-line bg-surface p-5 font-display text-xl leading-snug text-ink outline-none placeholder:text-muted/60 focus:border-gold focus:ring-4 focus:ring-gold/20"
          />
          <Button type="submit" size="lg" className="w-full" disabled={phase === "thinking"}>
            {phase === "thinking" ? <Loader2 className="size-4 animate-spin" /> : <Sparkles className="size-4" />}
            {phase === "thinking" ? "Je comprends…" : "Préparer la commande"}
          </Button>
          <button type="button" onClick={() => setTyped(false)} className="text-sm font-semibold text-muted hover:text-ink">
            Revenir au micro
          </button>
        </form>
      ) : (
        <>
          <p className="font-display text-3xl font-medium leading-tight text-ink sm:text-4xl">
            {phase === "recording" ? "Je vous écoute…" : phase === "thinking" ? "Je prépare la commande…" : (
              <>
                Dites-moi tout,
                <br />
                <span className="italic text-clay">comme à votre apprenti.</span>
              </>
            )}
          </p>
          <p className="mt-3 max-w-sm text-ink-soft">
            {phase === "idle" ? "Client, tenue, tissu, date, prix, acompte. Dans l'ordre que vous voulez." : phase === "recording" ? `0:${String(seconds).padStart(2, "0")}` : "Vous pourrez tout vérifier."}
          </p>

          <div className="relative my-12 grid place-items-center">
            {phase === "recording" && (
              <>
                <span className="absolute size-40 animate-pulse-ring rounded-full bg-clay/30" />
                <span className="absolute size-40 animate-pulse-ring rounded-full bg-clay/20 [animation-delay:0.8s]" />
              </>
            )}
            <button
              type="button"
              onClick={() => setPhase(phase === "recording" ? "thinking" : "recording")}
              disabled={phase === "thinking"}
              className={cn(
                "relative grid size-36 place-items-center rounded-full shadow-2xl transition-all duration-300 active:scale-95",
                phase === "recording" ? "bg-clay text-white" : "bg-gold text-gold-ink shadow-[0_20px_60px_-15px_var(--gold)]",
                phase === "thinking" && "bg-night text-gold",
              )}
              aria-label={phase === "recording" ? "Arrêter l'enregistrement" : "Commencer l'enregistrement"}
            >
              {phase === "thinking" ? <Loader2 className="size-12 animate-spin" /> : phase === "recording" ? <Square className="size-10 fill-current" /> : <Mic className="size-14" />}
            </button>
          </div>

          {phase === "recording" ? (
            <div className="flex h-12 w-full max-w-xs items-center gap-1" aria-hidden>
              {Array.from({ length: 32 }).map((_, i) => (
                <span key={i} className="wave-bar h-full flex-1 rounded-full bg-clay/70" style={{ animationDelay: `${(i % 8) * 0.09}s` }} />
              ))}
            </div>
          ) : (
            phase === "idle" && (
              <button type="button" onClick={() => setTyped(true)} className="flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-muted hover:bg-surface-2 hover:text-ink">
                <Keyboard className="size-4" /> Écrire plutôt
              </button>
            )
          )}
        </>
      )}
    </div>
  );
}

function DraftField({
  label,
  value,
  onChange,
  badge,
  inputMode,
}: {
  label: string;
  value: string;
  onChange: (v: string) => void;
  badge?: string;
  inputMode?: React.HTMLAttributes<HTMLInputElement>["inputMode"];
}) {
  return (
    <label className="block">
      <span className="flex items-center gap-2 text-xs font-bold text-muted">
        {label}
        {badge && <span className="rounded-full bg-leaf-soft px-2 py-0.5 text-[0.65rem] text-leaf">{badge}</span>}
      </span>
      <input
        value={value}
        inputMode={inputMode}
        onChange={(e) => onChange(e.target.value)}
        className="mt-1 h-11 w-full rounded-xl border border-line bg-surface-2/60 px-3 font-semibold text-ink outline-none transition focus:border-gold focus:bg-surface focus:ring-4 focus:ring-gold/20"
      />
    </label>
  );
}
