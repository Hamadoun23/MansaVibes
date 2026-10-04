"use client";

import { Check, Mic, Sparkles } from "lucide-react";
import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";

const transcript =
  "Nouvelle cliente Awa Sy, 76 12 34 56. Boubou en bazin bleu nuit pour samedi, quarante-cinq mille. Elle a donné vingt mille en Wave.";

const fields = [
  { label: "Cliente", value: "Awa Sy", hint: "nouvelle fiche" },
  { label: "Téléphone", value: "76 12 34 56" },
  { label: "Tenue", value: "Boubou · bazin bleu nuit" },
  { label: "Livraison", value: "Samedi 10 oct." },
  { label: "Prix", value: "45 000 F" },
  { label: "Acompte", value: "20 000 F · Wave", hint: "reste 25 000 F" },
];

/** Démo en boucle : écoute → transcription → formulaire pré-rempli → validé. */
export function VoiceDemo() {
  const [chars, setChars] = useState(0);
  const [filled, setFilled] = useState(0);
  const [phase, setPhase] = useState<"listen" | "fill" | "done">("listen");

  useEffect(() => {
    let t: ReturnType<typeof setTimeout>;
    if (phase === "listen") {
      if (chars < transcript.length) t = setTimeout(() => setChars((c) => Math.min(transcript.length, c + 2)), 28);
      else t = setTimeout(() => setPhase("fill"), 500);
    } else if (phase === "fill") {
      if (filled < fields.length) t = setTimeout(() => setFilled((f) => f + 1), 280);
      else t = setTimeout(() => setPhase("done"), 400);
    } else {
      t = setTimeout(() => {
        setChars(0);
        setFilled(0);
        setPhase("listen");
      }, 3800);
    }
    return () => clearTimeout(t);
  }, [chars, filled, phase]);

  return (
    <div className="grid gap-4 md:grid-cols-2">
      {/* note vocale */}
      <div className="rounded-[2rem] border border-white/10 bg-white/[0.04] p-5 backdrop-blur sm:p-6">
        <div className="flex items-center gap-3">
          <span className="relative grid size-12 place-items-center rounded-full bg-gold text-gold-ink">
            {phase === "listen" && <span className="absolute inset-0 animate-pulse-ring rounded-full bg-gold" />}
            <Mic className="relative size-5" />
          </span>
          <div>
            <p className="text-sm font-bold text-white">{phase === "listen" ? "Je vous écoute…" : "Note comprise"}</p>
            <p className="text-xs text-white/50">0:{phase === "listen" ? String(Math.floor(chars / 12)).padStart(2, "0") : "11"} · wolof, français, bambara</p>
          </div>
        </div>
        <div className="mt-5 flex h-10 items-center gap-[3px]" aria-hidden>
          {Array.from({ length: 42 }).map((_, i) => (
            <span
              key={i}
              className={cn("h-full flex-1 rounded-full", phase === "listen" ? "wave-bar bg-gold/80" : "bg-white/15")}
              style={{ animationDelay: `${(i % 7) * 0.1}s`, transform: phase === "listen" ? undefined : `scaleY(${0.2 + ((i * 37) % 10) / 14})` }}
            />
          ))}
        </div>
        <p className="mt-5 min-h-[7.5rem] font-display text-xl leading-snug text-white/90 sm:text-2xl">
          « {transcript.slice(0, chars)}
          {phase === "listen" && <span className="ml-0.5 inline-block h-5 w-0.5 animate-pulse bg-gold align-middle" />}
          {chars >= transcript.length && " »"}
        </p>
      </div>

      {/* formulaire pré-rempli */}
      <div className="rounded-[2rem] bg-surface p-5 text-ink shadow-2xl sm:p-6">
        <div className="flex items-center justify-between">
          <p className="flex items-center gap-2 text-sm font-bold">
            <Sparkles className="size-4 text-gold" /> Nouvelle commande
          </p>
          <span className="rounded-full bg-surface-2 px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-muted">À vérifier</span>
        </div>
        <dl className="mt-4 divide-y divide-line">
          {fields.map((f, i) => (
            <div key={f.label} className="flex items-center justify-between gap-3 py-2.5">
              <dt className="text-xs font-semibold text-muted">{f.label}</dt>
              <dd
                className={cn(
                  "text-right text-sm font-bold transition-all duration-500",
                  i < filled ? "translate-x-0 opacity-100" : "translate-x-2 opacity-0",
                )}
              >
                {f.value}
                {f.hint && <span className="block text-[0.7rem] font-semibold text-leaf">{f.hint}</span>}
              </dd>
            </div>
          ))}
        </dl>
        <button
          type="button"
          tabIndex={-1}
          className={cn(
            "mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-full font-bold transition-all duration-500",
            phase === "done" ? "bg-leaf text-white" : "bg-surface-2 text-muted",
          )}
        >
          {phase === "done" ? (
            <>
              <Check className="size-4" /> Enregistrée · reçu WhatsApp prêt
            </>
          ) : (
            "Valider d'un tap"
          )}
        </button>
      </div>
    </div>
  );
}
