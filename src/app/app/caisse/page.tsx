import { ArrowDownLeft, Lock } from "lucide-react";
import type { Metadata } from "next";
import Link from "next/link";
import { PageBody, PageHeader } from "@/components/app/shell";
import { Avatar, Button, Card } from "@/components/ui";
import { clientById, orderById, payMethodLabel, payments, type PayMethod } from "@/lib/demo";
import { cn, fcfa } from "@/lib/utils";

export const metadata: Metadata = { title: "Caisse" };

const methodStyle: Record<PayMethod, string> = {
  wave: "bg-[#1dc8f2]",
  orange: "bg-[#ff7900]",
  especes: "bg-leaf",
  carte: "bg-sky",
};

export default function CashPage() {
  const total = payments.reduce((s, p) => s + p.amount, 0);
  const byMethod = (Object.keys(payMethodLabel) as PayMethod[])
    .map((m) => ({ m, amount: payments.filter((p) => p.method === m).reduce((s, p) => s + p.amount, 0) }))
    .filter((x) => x.amount > 0);

  return (
    <>
      <PageHeader subtitle="Samedi" title="Caisse du jour" />
      <PageBody className="grid gap-5 lg:grid-cols-[1fr_1.3fr] lg:items-start">
        <div className="space-y-4">
          <Card className="p-5 sm:p-6">
            <p className="text-xs font-bold uppercase tracking-widest text-muted">Total encaissé</p>
            <p className="mt-2 font-display text-5xl font-semibold tracking-tight text-ink">{fcfa(total)}</p>
            <div className="mt-5 flex h-3 overflow-hidden rounded-full">
              {byMethod.map((x) => (
                <span key={x.m} className={methodStyle[x.m]} style={{ width: `${(x.amount / total) * 100}%` }} />
              ))}
            </div>
            <ul className="mt-5 space-y-3">
              {byMethod.map((x) => (
                <li key={x.m} className="flex items-center justify-between text-sm">
                  <span className="flex items-center gap-2.5 font-semibold text-ink">
                    <span className={cn("size-3 rounded-full", methodStyle[x.m])} />
                    {payMethodLabel[x.m]}
                  </span>
                  <span className="font-bold text-ink">{fcfa(x.amount)}</span>
                </li>
              ))}
            </ul>
          </Card>
          <Button variant="ink" size="lg" className="w-full">
            <Lock className="size-4" /> Clôturer la journée
          </Button>
          <p className="text-center text-xs text-muted">La clôture fige les montants et envoie le récapitulatif au gérant.</p>
        </div>

        <section>
          <h2 className="mb-3 text-sm font-bold uppercase tracking-widest text-muted">Mouvements · {payments.length}</h2>
          <ul className="divide-y divide-line overflow-hidden rounded-3xl border border-line bg-surface">
            {[...payments].reverse().map((p) => {
              const order = orderById(p.orderId)!;
              const client = clientById(order.clientId)!;
              return (
                <li key={p.id}>
                  <Link href={`/app/commandes/${order.id}`} className="flex items-center gap-3 p-4 hover:bg-surface-2/60">
                    <span className="relative">
                      <Avatar name={client.name} />
                      <span className={cn("absolute -bottom-0.5 -right-0.5 grid size-4 place-items-center rounded-full text-white ring-2 ring-surface", methodStyle[p.method])}>
                        <ArrowDownLeft className="size-2.5" />
                      </span>
                    </span>
                    <div className="min-w-0 flex-1">
                      <p className="truncate font-bold text-ink">{client.name}</p>
                      <p className="truncate text-sm text-muted">
                        {payMethodLabel[p.method]} · {order.garment}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-leaf">+{fcfa(p.amount)}</p>
                      <p className="text-xs text-muted">{p.when}</p>
                    </div>
                  </Link>
                </li>
              );
            })}
          </ul>
        </section>
      </PageBody>
    </>
  );
}
