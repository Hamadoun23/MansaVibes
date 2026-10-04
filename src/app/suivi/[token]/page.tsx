import { Check, MessageCircle, Phone, Ruler } from "lucide-react";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { Bogolan, LogoMark } from "@/components/brand";
import { atelier, clientById, orderByToken, orders, statusMeta, statusOrder } from "@/lib/demo";
import { cn, dueLabel, fcfa } from "@/lib/utils";

export const metadata: Metadata = { title: "Suivi de commande", robots: { index: false } };

// démo : seuls les identifiants connus existent ; les autres donnent une vraie 404
export const dynamicParams = false;

export function generateStaticParams() {
  return orders.map((o) => ({ token: o.token }));
}

/** Page publique, sans compte : le client suit sa tenue via un lien privé. */
export default async function TrackingPage({ params }: PageProps<"/suivi/[token]">) {
  const { token } = await params;
  const order = orderByToken(token);
  if (!order) notFound();
  const client = clientById(order.clientId)!;
  const step = statusMeta[order.status].step;
  const rest = order.price - order.paid;
  const atelierPhone = atelier.phone.replace(/[^\d]/g, "");
  const steps = statusOrder.slice(0, 5);

  return (
    <div className="min-h-dvh pb-10">
      <header className="relative overflow-hidden bg-night px-4 pb-24 pt-[max(1.5rem,env(safe-area-inset-top))] text-white grain">
        <Bogolan className="text-gold/[0.08]" id="suivi-bogolan" />
        <div className="relative mx-auto max-w-md">
          <div className="flex items-center gap-2.5">
            <LogoMark className="size-8" />
            <p className="text-sm font-bold">{atelier.name}</p>
          </div>
          <p className="mt-8 text-sm text-white/60">Bonjour {client.name.split(" ")[0]},</p>
          <h1 className="mt-1 font-display text-4xl font-medium leading-tight">
            {order.status === "prete" ? (
              <>
                Votre tenue est <span className="italic text-gold">prête&nbsp;!</span>
              </>
            ) : order.status === "livree" ? (
              <>Merci pour votre confiance.</>
            ) : (
              <>
                Votre tenue <span className="italic text-gold">avance.</span>
              </>
            )}
          </h1>
        </div>
      </header>

      <main className="relative mx-auto -mt-16 max-w-md space-y-3 px-4">
        <section className="rounded-2xl border border-line bg-surface p-5 shadow-xl">
          <div className="flex items-start justify-between gap-3">
            <div>
              <p className="font-display text-xl font-semibold text-ink">{order.garment}</p>
              <p className="text-sm text-muted">{order.fabric}</p>
            </div>
            <span className="shrink-0 rounded-full bg-surface-2 px-2.5 py-1 text-xs font-bold text-ink-soft">{order.ref}</span>
          </div>
          <ol className="mt-5 space-y-0">
            {steps.map((s, i) => (
              <li key={s} className="relative flex gap-3 pb-4 last:pb-0">
                {i < steps.length - 1 && <span className={cn("absolute left-[0.9rem] top-8 h-[calc(100%-1.5rem)] w-0.5", i < step ? "bg-leaf" : "bg-surface-3")} />}
                <span
                  className={cn(
                    "relative grid size-7.5 shrink-0 place-items-center rounded-full text-xs font-bold",
                    i < step || (i === step && s === "prete") ? "bg-leaf text-white" : i === step ? "bg-gold text-gold-ink ring-4 ring-gold/25" : "bg-surface-2 text-muted",
                  )}
                >
                  {i < step || (i === step && s === "prete") ? <Check className="size-4" /> : i + 1}
                </span>
                <div className="pt-1">
                  <p className={cn("text-sm font-bold", i > step ? "text-muted" : "text-ink")}>{s === "nouvelle" ? "Commande reçue" : statusMeta[s].label}</p>
                  {i === step && s !== "prete" && <p className="text-xs font-semibold text-clay">En cours</p>}
                </div>
              </li>
            ))}
          </ol>
          <p className="mt-5 rounded-2xl bg-surface-2 px-4 py-3 text-sm text-ink-soft">
            Livraison prévue : <strong className="text-ink">{dueLabel(order.dueIn).toLowerCase()}</strong>
          </p>
        </section>

        <section className="flex items-center justify-between rounded-2xl bg-night p-5 text-white">
          <div>
            <p className="text-xs text-white/60">{rest > 0 ? "Reste à payer" : "Paiement"}</p>
            <p className="font-display text-2xl font-semibold">{rest > 0 ? fcfa(rest) : "Soldé ✓"}</p>
            <p className="text-xs text-white/50">
              {fcfa(order.paid)} versés sur {fcfa(order.price)}
            </p>
          </div>
          {rest > 0 && <span className="rounded-full bg-gold px-4 py-2.5 text-sm font-bold text-gold-ink">Payer par Wave</span>}
        </section>

        <details className="group rounded-2xl border border-line bg-surface">
          <summary className="flex cursor-pointer list-none items-center gap-3 p-5 font-bold text-ink [&::-webkit-details-marker]:hidden">
            <Ruler className="size-4 text-gold" /> Mes mesures
            <span className="ml-auto text-sm text-muted transition group-open:rotate-180">⌄</span>
          </summary>
          <div className="grid grid-cols-2 gap-2 px-5 pb-5">
            {client.measurements.map((m) => (
              <div key={m.key} className="rounded-2xl bg-surface-2 px-3 py-2">
                <p className="text-[0.7rem] font-semibold text-muted">{m.label}</p>
                <p className="font-display text-lg font-semibold text-ink">{m.value} cm</p>
              </div>
            ))}
          </div>
        </details>

        <div className="grid grid-cols-2 gap-2 pt-2">
          <a href={`tel:+${atelierPhone}`} className="flex h-12 items-center justify-center gap-2 rounded-full border border-line bg-surface text-sm font-bold text-ink">
            <Phone className="size-4" /> Appeler
          </a>
          <a href={`https://wa.me/${atelierPhone}`} target="_blank" rel="noreferrer" className="flex h-12 items-center justify-center gap-2 rounded-full bg-[#25d366] text-sm font-bold text-white">
            <MessageCircle className="size-4" /> WhatsApp
          </a>
        </div>
        <p className="pt-4 text-center text-xs text-muted">
          {atelier.city} · propulsé par <span className="font-bold text-ink">Mansa Vibes</span>
        </p>
      </main>
    </div>
  );
}
