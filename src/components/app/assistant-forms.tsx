"use client";

import { Check, Mic, Plus, RotateCcw, Sparkles, UserCheck } from "lucide-react";
import Link from "next/link";
import { useState, type ReactNode } from "react";
import { Button, Card } from "@/components/ui";
import { measurementLabels } from "@/lib/demo";
import type { OrderDraft } from "@/lib/order-draft";
import { fcfa } from "@/lib/utils";

/* ───────── Éléments communs ───────── */

export function DraftField({
  label,
  value,
  onChange,
  inputMode,
  type = "text",
  autoComplete,
  suffix,
}: {
  label: string;
  value: string;
  onChange: (v: string) => void;
  inputMode?: React.HTMLAttributes<HTMLInputElement>["inputMode"];
  type?: string;
  autoComplete?: string;
  suffix?: string;
}) {
  return (
    <label className="block">
      <span className="flex items-center gap-2 text-xs font-bold text-muted">
        {label}
        {!value && <span className="rounded-full bg-surface-2 px-2 py-0.5 text-[0.65rem]">non précisé</span>}
      </span>
      <span className="relative mt-1 block">
        <input
          type={type}
          value={value}
          inputMode={inputMode}
          autoComplete={autoComplete}
          onChange={(e) => onChange(e.target.value)}
          className="h-11 w-full rounded-xl border border-line bg-surface-2/60 px-3 font-semibold text-ink outline-none transition focus:border-gold focus:bg-surface focus:ring-4 focus:ring-gold/20"
        />
        {suffix && <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-muted">{suffix}</span>}
      </span>
    </label>
  );
}

function ReviewShell({
  title,
  badge,
  children,
  footer,
  saved,
  savedText,
  onReset,
  onSave,
  canSave,
}: {
  title: string;
  badge: ReactNode;
  children: ReactNode;
  footer?: ReactNode;
  saved: boolean;
  savedText: string;
  onReset: () => void;
  onSave: () => void;
  canSave: boolean;
}) {
  return (
    <>
      <Card className="overflow-hidden">
        <div className="flex items-center justify-between gap-3 border-b border-line bg-gold-soft/40 px-5 py-3.5">
          <p className="flex items-center gap-2 font-bold text-ink">
            <Sparkles className="size-4 text-gold" /> {title}
          </p>
          {badge}
        </div>
        {children}
        {footer}
      </Card>
      {saved ? (
        <div className="rounded-2xl bg-leaf p-5 text-white">
          <p className="flex items-center gap-2 font-display text-xl font-semibold">
            <Check className="size-5" /> {savedText}
          </p>
          <p className="mt-1 text-sm text-white/80">Version de démonstration : l&apos;enregistrement en base arrive avec le backend.</p>
          <button type="button" onClick={onReset} className="mt-4 flex h-11 w-full items-center justify-center gap-2 rounded-full bg-white text-sm font-bold text-leaf">
            <Mic className="size-4" /> Autre chose
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-[auto_1fr] gap-2">
          <Button variant="outline" size="lg" onClick={onReset} aria-label="Recommencer">
            <RotateCcw className="size-4" />
          </Button>
          <Button size="lg" onClick={onSave} disabled={!canSave}>
            <Check className="size-4" /> Valider et enregistrer
          </Button>
        </div>
      )}
    </>
  );
}

function KnownClient({ clientId, name }: { clientId: string | null; name: string }) {
  if (!clientId) return <span className="rounded-full bg-leaf-soft px-2.5 py-1 text-[0.7rem] font-bold text-leaf">Nouveau client</span>;
  return (
    <Link href={`/app/clients/${clientId}`} className="flex items-center gap-1 rounded-full bg-sky-soft px-2.5 py-1 text-[0.7rem] font-bold text-sky">
      <UserCheck className="size-3" /> {name.split(" ")[0]} · déjà client
    </Link>
  );
}

/* ───────── Nouvelle commande ───────── */

type OrderForm = Record<"client" | "phone" | "garment" | "fabric" | "due" | "price" | "deposit" | "method" | "notes", string>;

export function OrderReview({ draft, clientId, onReset }: { draft: OrderDraft; clientId: string | null; onReset: () => void }) {
  const [saved, setSaved] = useState(false);
  const [form, setForm] = useState<OrderForm>({
    client: draft.client_name ?? "",
    phone: draft.phone ?? "",
    garment: draft.garment ?? "",
    fabric: draft.fabric ?? "",
    due: draft.due_date ?? "",
    price: draft.price != null ? String(draft.price) : "",
    deposit: draft.deposit != null ? String(draft.deposit) : "",
    method: draft.payment_method ?? "",
    notes: draft.notes ?? "",
  });
  const set = (key: keyof OrderForm) => (v: string) => setForm((f) => ({ ...f, [key]: v }));
  const rest = Math.max(0, (Number(form.price) || 0) - (Number(form.deposit) || 0));

  return (
    <ReviewShell
      title="Nouvelle commande"
      badge={<KnownClient clientId={clientId} name={form.client} />}
      saved={saved}
      savedText="Commande enregistrée"
      onReset={onReset}
      onSave={() => setSaved(true)}
      canSave={Boolean(form.client || form.garment)}
      footer={
        <div className="flex items-center justify-between border-t border-line px-5 py-3.5 text-sm">
          <span className="font-semibold text-muted">Reste à payer</span>
          <span className="font-display text-xl font-semibold text-ink">{fcfa(rest)}</span>
        </div>
      }
    >
      <div className="grid gap-x-4 gap-y-3 p-5 sm:grid-cols-2">
        <DraftField label="Client" value={form.client} onChange={set("client")} autoComplete="name" />
        <DraftField label="Téléphone" value={form.phone} onChange={set("phone")} inputMode="tel" />
        <DraftField label="Tenue" value={form.garment} onChange={set("garment")} />
        <DraftField label="Tissu" value={form.fabric} onChange={set("fabric")} />
        <DraftField label="Livraison" value={form.due} onChange={set("due")} type="date" />
        <DraftField label="Prix" value={form.price} onChange={(v) => set("price")(v.replace(/\D/g, ""))} inputMode="numeric" suffix="F" />
        <DraftField label="Acompte" value={form.deposit} onChange={(v) => set("deposit")(v.replace(/\D/g, ""))} inputMode="numeric" suffix="F" />
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
    </ReviewShell>
  );
}

/* ───────── Client et mesures ───────── */

const commonMeasures = ["poitrine", "taille", "bassin", "epaule", "manche", "longueur"];

export function ClientReview({
  mode,
  clientId,
  client,
  measurements,
  onReset,
}: {
  mode: "create" | "measurements";
  clientId: string | null;
  client: { name: string; phone: string; city: string };
  measurements: { key: string; value: number }[];
  onReset: () => void;
}) {
  const [saved, setSaved] = useState(false);
  const [info, setInfo] = useState(client);
  const heard = Object.fromEntries(measurements.map((m) => [m.key, String(m.value)]));
  const [measures, setMeasures] = useState<Record<string, string>>(() => {
    const keys = measurements.length ? measurements.map((m) => m.key) : commonMeasures;
    return Object.fromEntries(keys.map((k) => [k, heard[k] ?? ""]));
  });
  const missing = Object.keys(measurementLabels).filter((k) => !(k in measures));

  return (
    <ReviewShell
      title={mode === "create" ? "Nouveau client" : `Mesures de ${info.name.split(" ")[0]}`}
      badge={<KnownClient clientId={clientId} name={info.name} />}
      saved={saved}
      savedText={mode === "create" ? "Client enregistré" : "Mesures enregistrées"}
      onReset={onReset}
      onSave={() => setSaved(true)}
      canSave={Boolean(info.name)}
    >
      <div className="space-y-5 p-5">
        {mode === "create" && (
          <div className="grid gap-x-4 gap-y-3 sm:grid-cols-3">
            <DraftField label="Nom" value={info.name} onChange={(v) => setInfo({ ...info, name: v })} autoComplete="name" />
            <DraftField label="Téléphone" value={info.phone} onChange={(v) => setInfo({ ...info, phone: v })} inputMode="tel" />
            <DraftField label="Quartier" value={info.city} onChange={(v) => setInfo({ ...info, city: v })} />
          </div>
        )}
        <div>
          <p className="mb-2 font-mono text-[0.7rem] font-medium uppercase tracking-[0.12em] text-muted">Mesures · cm</p>
          <div className="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
            {Object.entries(measures).map(([key, value]) => (
              <DraftField
                key={key}
                label={measurementLabels[key] ?? key}
                value={value}
                onChange={(v) => setMeasures((m) => ({ ...m, [key]: v.replace(/[^\d.,]/g, "") }))}
                inputMode="decimal"
                suffix="cm"
              />
            ))}
          </div>
          {missing.length > 0 && (
            <div className="mt-3 flex flex-wrap gap-1.5">
              {missing.map((k) => (
                <button
                  key={k}
                  type="button"
                  onClick={() => setMeasures((m) => ({ ...m, [k]: "" }))}
                  className="flex items-center gap-1 rounded-full bg-surface-2 px-2.5 py-1 text-xs font-semibold text-ink-soft hover:text-ink"
                >
                  <Plus className="size-3" /> {measurementLabels[k]}
                </button>
              ))}
            </div>
          )}
        </div>
      </div>
    </ReviewShell>
  );
}
