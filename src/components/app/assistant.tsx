"use client";

import { AlertCircle, Check, Keyboard, Loader2, Mic, RotateCcw, Sparkles, Square } from "lucide-react";
import Link from "next/link";
import { useEffect, useState } from "react";
import { Button, Card } from "@/components/ui";
import type { OrderDraft } from "@/lib/order-draft";
import { useDictation } from "@/lib/use-dictation";
import { cn, fcfa } from "@/lib/utils";

type Phase = "idle" | "recording" | "stopping" | "thinking" | "review" | "saved";

type Form = { client: string; phone: string; garment: string; fabric: string; due: string; price: string; deposit: string; method: string; notes: string };

const example = "Nouvelle cliente Awa Sy, 76 12 34 56. Boubou en bazin bleu nuit pour samedi, 45 000. Elle a donné 20 000 en Wave.";

const toForm = (d: OrderDraft): Form => ({
  client: d.client_name ?? "",
  phone: d.phone ?? "",
  garment: d.garment ?? "",
  fabric: d.fabric ?? "",
  due: d.due_date ?? "",
  price: d.price != null ? String(d.price) : "",
  deposit: d.deposit != null ? String(d.deposit) : "",
  method: d.payment_method ?? "",
  notes: d.notes ?? "",
});

function localToday() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

export function Assistant() {
  const [phase, setPhase] = useState<Phase>("idle");
  const [typed, setTyped] = useState(false);
  const [text, setText] = useState("");
  const [sent, setSent] = useState("");
  const [form, setForm] = useState<Form | null>(null);
  const [source, setSource] = useState<"claude" | "local" | null>(null);
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
    try {
      const res = await fetch("/api/assistant", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transcript, today: localToday() }),
      });
      if (!res.ok) throw new Error(String(res.status));
      const data = (await res.json()) as { draft: OrderDraft; source: "claude" | "local" };
      setForm(toForm(data.draft));
      setSource(data.source);
      setPhase("review");
    } catch {
      setFailure("Impossible de joindre le serveur. Vérifiez la connexion et réessayez.");
      setPhase("idle");
    }
  }

  function startRecording() {
    setFailure(null);
    setSeconds(0);
    dictation.start();
    setPhase("recording");
  }

  function reset() {
    setPhase("idle");
    setText("");
    setSent("");
    setForm(null);
    setFailure(null);
  }

  /* ───────── Vérification du brouillon ───────── */
  if ((phase === "review" || phase === "saved") && form) {
    const price = Number(form.price) || 0;
    const deposit = Number(form.deposit) || 0;
    const set = (key: keyof Form) => (v: string) => setForm({ ...form, [key]: v });

    return (
      <div className="mx-auto max-w-xl space-y-4">
        <Card className="p-4">
          <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-muted">
            <Mic className="size-3.5" /> Vous avez dit
          </p>
          <p className="mt-2 font-display text-lg leading-snug text-ink">« {sent} »</p>
        </Card>

        <Card className="overflow-hidden">
          <div className="flex items-center justify-between gap-3 border-b border-line bg-gold-soft/40 px-5 py-3.5">
            <p className="flex items-center gap-2 font-bold text-ink">
              <Sparkles className="size-4 text-gold" /> Nouvelle commande
            </p>
            <span className="text-right text-xs font-semibold text-muted">
              {source === "claude" ? "Comprise par l'IA · vérifiez" : "Analyse simple · vérifiez bien"}
            </span>
          </div>
          <div className="grid gap-x-4 gap-y-3 p-5 sm:grid-cols-2">
            <DraftField label="Client" value={form.client} onChange={set("client")} autoComplete="name" />
            <DraftField label="Téléphone" value={form.phone} onChange={set("phone")} inputMode="tel" />
            <DraftField label="Tenue" value={form.garment} onChange={set("garment")} />
            <DraftField label="Tissu" value={form.fabric} onChange={set("fabric")} />
            <DraftField label="Livraison" value={form.due} onChange={set("due")} type="date" />
            <DraftField label="Prix (F)" value={form.price} onChange={(v) => set("price")(v.replace(/\D/g, ""))} inputMode="numeric" />
            <DraftField label="Acompte (F)" value={form.deposit} onChange={(v) => set("deposit")(v.replace(/\D/g, ""))} inputMode="numeric" />
            <label className="block">
              <span className="text-xs font-bold text-muted">Payé par</span>
              <select
                value={form.method}
                onChange={(e) => set("method")(e.target.value)}
                className="mt-1 h-11 w-full rounded-xl border border-line bg-surface-2/60 px-3 font-semibold text-ink outline-none focus:border-gold focus:ring-4 focus:ring-gold/20"
              >
                <option value="">—</option>
                <option value="wave">Wave</option>
                <option value="orange">Orange Money</option>
                <option value="especes">Espèces</option>
                <option value="carte">Carte</option>
              </select>
            </label>
            {form.notes && (
              <div className="sm:col-span-2">
                <DraftField label="Notes" value={form.notes} onChange={set("notes")} />
              </div>
            )}
          </div>
          <div className="flex items-center justify-between border-t border-line px-5 py-3.5 text-sm">
            <span className="font-semibold text-muted">Reste à payer</span>
            <span className="font-display text-xl font-semibold text-ink">{fcfa(Math.max(0, price - deposit))}</span>
          </div>
        </Card>

        {phase === "saved" ? (
          <div className="rounded-3xl bg-leaf p-5 text-white">
            <p className="flex items-center gap-2 font-display text-xl font-semibold">
              <Check className="size-5" /> Commande enregistrée
            </p>
            <p className="mt-1 text-sm text-white/80">Version de démonstration : l&apos;enregistrement en base arrive avec le backend.</p>
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
            <Button size="lg" onClick={() => setPhase("saved")} disabled={!form.client && !form.garment}>
              <Check className="size-4" /> Valider et enregistrer
            </Button>
          </div>
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

      {showTyping ? (
        <form
          className="w-full space-y-3"
          onSubmit={(e) => {
            e.preventDefault();
            if (text.trim()) void understand(text.trim());
          }}
        >
          {unsupported && (
            <p className="text-sm text-muted">La dictée n&apos;est pas disponible sur ce navigateur (utilisez Chrome, Edge ou Safari). Écrivez la commande :</p>
          )}
          <textarea
            value={text}
            onChange={(e) => setText(e.target.value)}
            rows={5}
            autoFocus
            placeholder={example}
            className="w-full rounded-3xl border border-line bg-surface p-5 font-display text-xl leading-snug text-ink outline-none placeholder:text-muted/60 focus:border-gold focus:ring-4 focus:ring-gold/20"
          />
          <Button type="submit" size="lg" className="w-full" disabled={busy || !text.trim()}>
            {phase === "thinking" ? <Loader2 className="size-4 animate-spin" /> : <Sparkles className="size-4" />}
            {phase === "thinking" ? "Je comprends…" : "Préparer la commande"}
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
              "Je prépare la commande…"
            ) : (
              <>
                Dites-moi tout,
                <br />
                <span className="italic text-clay">comme à votre apprenti.</span>
              </>
            )}
          </p>
          <p className="mt-3 max-w-sm text-ink-soft">
            {recording
              ? `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, "0")} · touchez le carré quand vous avez fini`
              : phase === "thinking"
                ? "Vous pourrez tout vérifier avant d'enregistrer."
                : "Client, téléphone, tenue, tissu, date, prix, acompte. Dans l'ordre que vous voulez."}
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
              aria-label={recording ? "Arrêter et préparer la commande" : "Commencer la dictée"}
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
  inputMode,
  type = "text",
  autoComplete,
}: {
  label: string;
  value: string;
  onChange: (v: string) => void;
  inputMode?: React.HTMLAttributes<HTMLInputElement>["inputMode"];
  type?: string;
  autoComplete?: string;
}) {
  return (
    <label className="block">
      <span className="flex items-center gap-2 text-xs font-bold text-muted">
        {label}
        {!value && <span className="rounded-full bg-surface-2 px-2 py-0.5 text-[0.65rem]">non précisé</span>}
      </span>
      <input
        type={type}
        value={value}
        inputMode={inputMode}
        autoComplete={autoComplete}
        onChange={(e) => onChange(e.target.value)}
        className="mt-1 h-11 w-full rounded-xl border border-line bg-surface-2/60 px-3 font-semibold text-ink outline-none transition focus:border-gold focus:bg-surface focus:ring-4 focus:ring-gold/20"
      />
    </label>
  );
}
