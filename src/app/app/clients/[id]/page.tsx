import { ArrowLeft, MessageCircle, Phone, Ruler } from "lucide-react";
import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { OrderRow } from "@/components/app/order-row";
import { PageBody, PageHeader } from "@/components/app/shell";
import { Bogolan } from "@/components/brand";
import { Avatar, Card } from "@/components/ui";
import { clientById, clients, ordersOf } from "@/lib/demo";
import { fcfa } from "@/lib/utils";

export function generateStaticParams() {
  return clients.map((c) => ({ id: c.id }));
}

export async function generateMetadata({ params }: PageProps<"/app/clients/[id]">): Promise<Metadata> {
  const { id } = await params;
  return { title: clientById(id)?.name ?? "Client" };
}

export default async function ClientPage({ params }: PageProps<"/app/clients/[id]">) {
  const { id } = await params;
  const client = clientById(id);
  if (!client) notFound();
  const os = ordersOf(client.id);
  const total = os.reduce((s, o) => s + o.price, 0);
  const due = os.reduce((s, o) => s + (o.price - o.paid), 0);
  const phone = client.phone.replace(/\s/g, "");

  return (
    <>
      <PageHeader
        back={
          <Link href="/app/clients" className="grid size-10 shrink-0 place-items-center rounded-full border border-line bg-surface text-ink" aria-label="Retour aux clients">
            <ArrowLeft className="size-5" />
          </Link>
        }
        subtitle="Fiche client"
        title={client.name}
      />
      <PageBody className="space-y-5">
        <div className="relative overflow-hidden rounded-[2rem] bg-night p-5 text-white grain sm:p-6">
          <Bogolan className="text-gold/[0.07]" id="client-bogolan" />
          <div className="relative flex items-center gap-4">
            <Avatar name={client.name} size="lg" className="ring-4 ring-white/10" />
            <div className="min-w-0">
              <p className="truncate font-display text-2xl font-semibold">{client.name}</p>
              <p className="text-sm text-white/60">
                {client.phone} · {client.city}
              </p>
            </div>
          </div>
          <div className="relative mt-5 grid grid-cols-3 gap-2 text-center">
            {[
              ["Commandes", String(os.length)],
              ["Total", fcfa(total)],
              ["Doit", due > 0 ? fcfa(due) : "0 F"],
            ].map(([l, v]) => (
              <div key={l} className="rounded-2xl bg-white/[0.07] px-2 py-3">
                <p className="font-display text-lg font-semibold sm:text-xl">{v}</p>
                <p className="text-[0.7rem] font-semibold text-white/55">{l}</p>
              </div>
            ))}
          </div>
          <div className="relative mt-4 grid grid-cols-2 gap-2">
            <a href={`tel:+221${phone}`} className="flex h-11 items-center justify-center gap-2 rounded-full bg-white/10 text-sm font-bold hover:bg-white/15">
              <Phone className="size-4" /> Appeler
            </a>
            <a href={`https://wa.me/221${phone}`} target="_blank" rel="noreferrer" className="flex h-11 items-center justify-center gap-2 rounded-full bg-[#25d366] text-sm font-bold hover:brightness-105">
              <MessageCircle className="size-4" /> WhatsApp
            </a>
          </div>
        </div>

        <section>
          <h2 className="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-muted">
            <Ruler className="size-4 text-gold" /> Mesures <span className="font-semibold normal-case tracking-normal">· en cm</span>
          </h2>
          <Card className="grid grid-cols-2 gap-px overflow-hidden bg-line sm:grid-cols-3 lg:grid-cols-4">
            {client.measurements.map((m) => (
              <div key={m.key} className="bg-surface p-4">
                <p className="text-xs font-semibold text-muted">{m.label}</p>
                <p className="mt-1 font-display text-3xl font-semibold text-ink">{m.value}</p>
              </div>
            ))}
          </Card>
        </section>

        {client.notes && (
          <div className="rounded-3xl bg-gold-soft/60 p-5">
            <p className="text-xs font-bold uppercase tracking-widest text-muted">Note</p>
            <p className="mt-1.5 font-display text-lg leading-snug text-ink">{client.notes}</p>
          </div>
        )}

        <section>
          <h2 className="mb-3 text-sm font-bold uppercase tracking-widest text-muted">Commandes</h2>
          <div className="grid gap-2">
            {os.map((o) => (
              <OrderRow key={o.id} order={o} />
            ))}
          </div>
        </section>
      </PageBody>
    </>
  );
}
