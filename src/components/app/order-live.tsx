"use client";

import { Check, MessageCircle, Plus, Sparkles, Wallet, X } from "lucide-react";
import { useState } from "react";
import { Badge, Button, Card } from "@/components/ui";
import { payMethodLabel, statusMeta, statusOrder, type Client, type Order, type OrderStatus, type PayMethod, type Payment } from "@/lib/demo";
import { cn, fcfa } from "@/lib/utils";

/** Partie interactive d'une commande : avancement, encaissement, message WhatsApp (état local en démo). */
/** Pré-remplissage venu de l'assistant vocal (paramètres d'URL). */
export type AssistantSuggestion = { status?: OrderStatus; cash?: { amount: number; method: PayMethod | null } };

export function OrderLive({
  order,
  client,
  initialPayments,
  suggestion,
}: {
  order: Order;
  client: Client;
  initialPayments: Payment[];
  suggestion?: AssistantSuggestion;
}) {
  const [status, setStatus] = useState(suggestion?.status ?? order.status);
  const [payments, setPayments] = useState(initialPayments);
  const [paidExtra, setPaidExtra] = useState(0);
  const [cashOpen, setCashOpen] = useState(Boolean(suggestion?.cash));
  const [suggested, setSuggested] = useState(Boolean(suggestion?.status && suggestion.status !== order.status));

  const paid = order.paid + paidExtra;
  const rest = Math.max(0, order.price - paid);
  const step = statusMeta[status].step;
  const next = statusOrder[step + 1];

  const firstName = client.name.split(" ")[0];
  const message =
    status === "prete" || status === "livree"
      ? `Bonjour ${firstName} 👋 Votre ${order.garment.toLowerCase()} est prêt(e) ✨${rest > 0 ? ` Reste à régler : ${fcfa(rest)}.` : ""} Suivi : mansa.app/s/${order.token}`
      : `Bonjour ${firstName}, nous avons bien reçu votre commande (${order.garment.toLowerCase()}). ${paid > 0 ? `Acompte reçu : ${fcfa(paid)}. ` : ""}Suivez l'avancement ici : mansa.app/s/${order.token}`;
  const waHref = `https://wa.me/221${client.phone.replace(/\s/g, "")}?text=${encodeURIComponent(message)}`;

  return (
    <div className="space-y-4">
      {suggested && (
        <div className="flex items-center gap-3 rounded-2xl bg-gold-soft/60 p-4">
          <Sparkles className="size-5 shrink-0 text-gold" />
          <p className="flex-1 text-sm font-semibold text-ink">
            Passée en « {statusMeta[status].label} » par l&apos;assistant.
          </p>
          <button
            type="button"
            onClick={() => {
              setStatus(order.status);
              setSuggested(false);
            }}
            className="text-sm font-bold text-clay hover:underline"
          >
            Annuler
          </button>
        </div>
      )}

      {/* avancement */}
      <Card className="p-5">
        <div className="flex items-center justify-between">
          <p className="font-mono text-[0.7rem] font-medium uppercase tracking-[0.12em] text-muted">Avancement</p>
          <Badge tone={statusMeta[status].tone}>{statusMeta[status].label}</Badge>
        </div>
        <ol className="mt-5 flex items-center">
          {statusOrder.map((s, i) => (
            <li key={s} className="flex flex-1 items-center last:flex-none">
              <button
                type="button"
                onClick={() => setStatus(s)}
                className={cn(
                  "grid size-9 shrink-0 place-items-center rounded-full text-xs font-bold transition",
                  i < step && "bg-leaf text-white",
                  i === step && "bg-gold text-gold-ink ring-4 ring-gold/25",
                  i > step && "bg-surface-2 text-muted",
                )}
                aria-label={statusMeta[s].label}
                aria-current={i === step ? "step" : undefined}
              >
                {i < step ? <Check className="size-4" /> : i + 1}
              </button>
              {i < statusOrder.length - 1 && <span className={cn("mx-1 h-0.5 flex-1 rounded-full", i < step ? "bg-leaf" : "bg-surface-3")} />}
            </li>
          ))}
        </ol>
        <div className="mt-2 flex justify-between text-[0.65rem] font-semibold text-muted">
          <span>Nouvelle</span>
          <span>Livrée</span>
        </div>
        {next && (
          <Button variant="ink" className="mt-5 w-full" onClick={() => setStatus(next)}>
            Passer à « {statusMeta[next].label} »
          </Button>
        )}
      </Card>

      {/* paiement */}
      <Card className="overflow-hidden">
        <div className="p-5">
          <div className="flex items-end justify-between gap-4">
            <div>
              <p className="font-mono text-[0.7rem] font-medium uppercase tracking-[0.12em] text-muted">Reste à payer</p>
              <p className={cn("mt-1 font-display text-4xl font-semibold tracking-tight", rest === 0 ? "text-leaf" : "text-ink")}>
                {rest === 0 ? "Soldé" : fcfa(rest)}
              </p>
            </div>
            <p className="text-right text-sm text-muted">
              {fcfa(paid)} versés
              <br />
              sur {fcfa(order.price)}
            </p>
          </div>
          <div className="mt-4 h-2.5 overflow-hidden rounded-full bg-surface-3">
            <div className="h-full rounded-full bg-gradient-to-r from-gold to-leaf transition-all duration-700" style={{ width: `${(paid / order.price) * 100}%` }} />
          </div>
        </div>
        {payments.length > 0 && (
          <ul className="divide-y divide-line border-t border-line">
            {payments.map((p) => (
              <li key={p.id} className="flex items-center justify-between px-5 py-3 text-sm">
                <span className="flex items-center gap-2 font-semibold text-ink">
                  <Wallet className="size-4 text-muted" /> {payMethodLabel[p.method]}
                  <span className="font-normal text-muted">· {p.when}</span>
                </span>
                <span className="font-bold text-leaf">+{fcfa(p.amount)}</span>
              </li>
            ))}
          </ul>
        )}
        {rest > 0 && (
          <div className="border-t border-line p-3">
            {cashOpen ? (
              <CashForm
                rest={rest}
                initialAmount={suggestion?.cash?.amount}
                initialMethod={suggestion?.cash?.method ?? undefined}
                onCancel={() => setCashOpen(false)}
                onSubmit={(amount, method) => {
                  setPaidExtra((x) => x + amount);
                  setPayments((list) => [...list, { id: `n${list.length}`, orderId: order.id, amount, method, when: "à l'instant" }]);
                  setCashOpen(false);
                }}
              />
            ) : (
              <Button className="w-full" onClick={() => setCashOpen(true)}>
                <Plus className="size-4" /> Encaisser
              </Button>
            )}
          </div>
        )}
      </Card>

      {/* whatsapp */}
      <Card className="p-5">
        <p className="font-mono text-[0.7rem] font-medium uppercase tracking-[0.12em] text-muted">Message prêt à envoyer</p>
        <p className="mt-3 rounded-2xl rounded-bl-md bg-[#d9fdd3] p-3.5 text-sm leading-relaxed text-[#111b21]">{message}</p>
        <a
          href={waHref}
          target="_blank"
          rel="noreferrer"
          className="mt-4 flex h-11 w-full items-center justify-center gap-2 rounded-full bg-[#25d366] font-bold text-white transition hover:brightness-105"
        >
          <MessageCircle className="size-4" /> Envoyer sur WhatsApp
        </a>
      </Card>
    </div>
  );
}

function CashForm({
  rest,
  initialAmount,
  initialMethod,
  onSubmit,
  onCancel,
}: {
  rest: number;
  initialAmount?: number;
  initialMethod?: PayMethod;
  onSubmit: (amount: number, method: PayMethod) => void;
  onCancel: () => void;
}) {
  const [amount, setAmount] = useState(String(Math.min(initialAmount ?? rest, rest)));
  const [method, setMethod] = useState<PayMethod>(initialMethod ?? "wave");
  const value = Number(amount.replace(/\D/g, ""));

  return (
    <form
      className="space-y-3 p-2"
      onSubmit={(e) => {
        e.preventDefault();
        if (value > 0) onSubmit(Math.min(value, rest), method);
      }}
    >
      <div className="flex items-center justify-between">
        <p className="font-bold text-ink">Encaisser</p>
        <button type="button" onClick={onCancel} className="grid size-8 place-items-center rounded-full text-muted hover:bg-surface-2" aria-label="Annuler">
          <X className="size-4" />
        </button>
      </div>
      <label className="relative block">
        <input
          inputMode="numeric"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          className="h-14 w-full rounded-2xl border border-line bg-surface-2 px-4 pr-12 font-display text-2xl font-semibold text-ink outline-none focus:border-gold"
          aria-label="Montant"
        />
        <span className="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-muted">F</span>
      </label>
      <div className="flex gap-2">
        {[rest, Math.round(rest / 2 / 500) * 500].filter((v, i, a) => v > 0 && a.indexOf(v) === i).map((v) => (
          <button key={v} type="button" onClick={() => setAmount(String(v))} className="rounded-full bg-surface-2 px-3 py-1.5 text-xs font-bold text-ink-soft hover:text-ink">
            {v === rest ? "Tout le reste" : "La moitié"} · {fcfa(v)}
          </button>
        ))}
      </div>
      <div className="grid grid-cols-3 gap-2">
        {(["wave", "orange", "especes"] as PayMethod[]).map((m) => (
          <button
            key={m}
            type="button"
            onClick={() => setMethod(m)}
            aria-pressed={method === m}
            className={cn("h-11 rounded-2xl border text-sm font-bold transition", method === m ? "border-ink bg-ink text-bg" : "border-line text-ink-soft")}
          >
            {payMethodLabel[m].replace(" Money", "")}
          </button>
        ))}
      </div>
      <Button type="submit" className="w-full">
        Valider {value > 0 ? fcfa(Math.min(value, rest)) : ""}
      </Button>
    </form>
  );
}
