import { ArrowLeft, Link2, Phone, Scissors, Shirt, User } from "lucide-react";
import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { OrderLive, type AssistantSuggestion } from "@/components/app/order-live";
import { PageBody, PageHeader } from "@/components/app/shell";
import { Avatar, Card } from "@/components/ui";
import { clientById, orderById, orders, paymentsOf, statusOrder, type OrderStatus, type PayMethod } from "@/lib/demo";
import { dueLabel } from "@/lib/utils";

// démo : seuls les identifiants connus existent ; les autres donnent une vraie 404
export const dynamicParams = false;

export function generateStaticParams() {
  return orders.map((o) => ({ id: o.id }));
}

export async function generateMetadata({ params }: PageProps<"/app/commandes/[id]">): Promise<Metadata> {
  const { id } = await params;
  const order = orderById(id);
  return { title: order ? `${order.ref} · ${order.garment}` : "Commande" };
}

const payMethods: PayMethod[] = ["wave", "orange", "especes", "carte"];

/** Lit le pré-remplissage envoyé par l'assistant : ?statut=prete, ?encaisser=15000&mode=wave */
function readSuggestion(sp: Record<string, string | string[] | undefined>): AssistantSuggestion | undefined {
  const one = (v: string | string[] | undefined) => (Array.isArray(v) ? v[0] : v);
  const statut = one(sp.statut);
  const amount = Number(one(sp.encaisser));
  const mode = one(sp.mode);
  const suggestion: AssistantSuggestion = {};
  if (statut && (statusOrder as string[]).includes(statut)) suggestion.status = statut as OrderStatus;
  if (amount > 0) suggestion.cash = { amount, method: mode && (payMethods as string[]).includes(mode) ? (mode as PayMethod) : null };
  return suggestion.status || suggestion.cash ? suggestion : undefined;
}

export default async function OrderPage({ params, searchParams }: PageProps<"/app/commandes/[id]">) {
  const { id } = await params;
  const order = orderById(id);
  if (!order) notFound();
  const suggestion = readSuggestion(await searchParams);
  const client = clientById(order.clientId)!;

  return (
    <>
      <PageHeader
        back={
          <Link href="/app/commandes" className="grid size-10 shrink-0 place-items-center rounded-full border border-line bg-surface text-ink" aria-label="Retour aux commandes">
            <ArrowLeft className="size-5" />
          </Link>
        }
        subtitle={`${order.ref} · ${dueLabel(order.dueIn)}`}
        title={order.garment}
      />
      <PageBody className="grid gap-4 lg:grid-cols-[1.3fr_1fr] lg:items-start">
        <OrderLive key={JSON.stringify(suggestion ?? null)} order={order} client={client} initialPayments={paymentsOf(order.id)} suggestion={suggestion} />

        <div className="space-y-4">
          <Card className="p-5">
            <Link href={`/app/clients/${client.id}`} className="flex items-center gap-3">
              <Avatar name={client.name} size="lg" />
              <div className="min-w-0">
                <p className="truncate font-display text-xl font-semibold text-ink">{client.name}</p>
                <p className="text-sm text-muted">
                  {client.city} · cliente depuis {client.since}
                </p>
              </div>
            </Link>
            <div className="mt-4 grid grid-cols-2 gap-2">
              <a href={`tel:+221${client.phone.replace(/\s/g, "")}`} className="flex h-11 items-center justify-center gap-2 rounded-full border border-line text-sm font-bold text-ink hover:bg-surface-2">
                <Phone className="size-4" /> Appeler
              </a>
              <Link href={`/suivi/${order.token}`} className="flex h-11 items-center justify-center gap-2 rounded-full border border-line text-sm font-bold text-ink hover:bg-surface-2">
                <Link2 className="size-4" /> Lien de suivi
              </Link>
            </div>
          </Card>

          <Card className="divide-y divide-line">
            <Detail icon={Shirt} label="Tissu" value={order.fabric} />
            <Detail icon={Scissors} label="Confiée à" value={order.tailor} />
            <Detail icon={User} label="Mesures" value={`${client.measurements.length} mesures enregistrées`} href={`/app/clients/${client.id}`} />
          </Card>

          {client.notes && (
            <div className="rounded-2xl bg-gold-soft/60 p-5">
              <p className="font-mono text-[0.7rem] font-medium uppercase tracking-[0.12em] text-muted">Note</p>
              <p className="mt-1.5 font-display text-lg leading-snug text-ink">{client.notes}</p>
            </div>
          )}
        </div>
      </PageBody>
    </>
  );
}

function Detail({ icon: Icon, label, value, href }: { icon: typeof Shirt; label: string; value: string; href?: string }) {
  const body = (
    <>
      <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-surface-2 text-ink">
        <Icon className="size-4" />
      </span>
      <div className="min-w-0">
        <p className="text-xs font-bold text-muted">{label}</p>
        <p className="truncate font-semibold text-ink">{value}</p>
      </div>
    </>
  );
  return href ? (
    <Link href={href} className="flex items-center gap-3 p-4 hover:bg-surface-2/50">
      {body}
    </Link>
  ) : (
    <div className="flex items-center gap-3 p-4">{body}</div>
  );
}
