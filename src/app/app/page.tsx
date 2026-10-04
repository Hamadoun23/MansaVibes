import { AlarmClock, ArrowUpRight, Bell, CheckCircle2, Mic, PackageCheck, TrendingUp } from "lucide-react";
import type { Metadata } from "next";
import Link from "next/link";
import { OrderRow } from "@/components/app/order-row";
import { PageBody, PageHeader } from "@/components/app/shell";
import { Bogolan } from "@/components/brand";
import { Card } from "@/components/ui";
import { atelier, todayStats, weekRevenue } from "@/lib/demo";
import { cn, fcfa } from "@/lib/utils";

export const metadata: Metadata = { title: "Aujourd'hui" };

export default function TodayPage() {
  const s = todayStats();
  const max = Math.max(...weekRevenue.map((d) => d.amount));
  const week = weekRevenue.reduce((sum, d) => sum + d.amount, 0);

  return (
    <>
      <PageHeader
        subtitle={atelier.name}
        title={`Bonjour ${atelier.owner.split(" ")[0]}`}
        action={
          <button className="relative grid size-11 place-items-center rounded-full border border-line bg-surface text-ink" aria-label="Notifications">
            <Bell className="size-5" />
            <span className="absolute right-2.5 top-2.5 size-2 rounded-full bg-clay ring-2 ring-surface" />
          </button>
        }
      />
      <PageBody className="space-y-6">
        {/* encaissements */}
        <section className="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
          <div className="relative overflow-hidden rounded-[2rem] bg-night p-5 text-white grain sm:p-6">
            <Bogolan className="text-gold/[0.06]" id="today-bogolan" />
            <div className="relative flex items-start justify-between">
              <div>
                <p className="text-xs font-bold uppercase tracking-widest text-white/55">Encaissé aujourd&apos;hui</p>
                <p className="mt-2 font-display text-5xl font-semibold tracking-tight">
                  {fcfa(s.cashedToday, "")}
                  <span className="ml-1.5 text-xl text-gold">F</span>
                </p>
                <p className="mt-1 flex items-center gap-1 text-sm font-semibold text-leaf">
                  <TrendingUp className="size-4" /> +18 % vs samedi dernier
                </p>
              </div>
              <Link href="/app/caisse" className="grid size-10 place-items-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Voir la caisse">
                <ArrowUpRight className="size-5" />
              </Link>
            </div>
            <div className="relative mt-6 flex h-24 items-end gap-2">
              {weekRevenue.map((d, i) => (
                <div key={d.day} className="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                  <div
                    className={cn("w-full rounded-lg", i === weekRevenue.length - 1 ? "bg-gold" : "bg-white/15")}
                    style={{ height: `${(d.amount / max) * 100}%` }}
                    title={`${d.day} : ${fcfa(d.amount)}`}
                  />
                  <span className="text-[0.65rem] font-semibold text-white/50">{d.day}</span>
                </div>
              ))}
            </div>
            <p className="relative mt-3 text-xs text-white/55">Cette semaine : {fcfa(week)}</p>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <Stat label="Reste à encaisser" value={fcfa(s.outstanding)} tone="gold" />
            <Stat label="Commandes en cours" value={String(s.activeCount)} />
            <Stat label="En retard" value={String(s.late.length)} tone="clay" icon={AlarmClock} />
            <Stat label="Prêtes à livrer" value={String(s.ready.length)} tone="leaf" icon={PackageCheck} />
          </div>
        </section>

        {/* dictée */}
        <Link
          href="/app/assistant"
          className="group flex items-center gap-4 rounded-[2rem] border border-dashed border-gold bg-gold-soft/50 p-4 transition hover:bg-gold-soft"
        >
          <span className="relative grid size-12 shrink-0 place-items-center rounded-full bg-gold text-gold-ink">
            <span className="absolute inset-0 animate-pulse-ring rounded-full bg-gold" />
            <Mic className="relative size-5" />
          </span>
          <div className="min-w-0">
            <p className="font-bold text-ink">Nouvelle commande ? Dites-la.</p>
            <p className="truncate text-sm text-ink-soft">« Kaftan en lin pour Ousmane, jeudi, 30 000… »</p>
          </div>
          <ArrowUpRight className="ml-auto size-5 shrink-0 text-ink transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
        </Link>

        <TaskGroup title="En retard" icon={AlarmClock} tone="text-clay" orders={s.late} empty="Aucun retard. Bravo !" />
        <TaskGroup title="À livrer aujourd'hui" icon={PackageCheck} tone="text-ink" orders={s.dueToday} empty="Rien à livrer aujourd'hui." />
        <TaskGroup title="Prêtes — prévenir le client" icon={CheckCircle2} tone="text-leaf" orders={s.ready} empty="Aucune tenue en attente." />
      </PageBody>
    </>
  );
}

function Stat({ label, value, tone, icon: Icon }: { label: string; value: string; tone?: "gold" | "clay" | "leaf"; icon?: typeof AlarmClock }) {
  return (
    <Card className="flex flex-col justify-between p-4">
      <p className="flex items-center gap-1.5 text-xs font-bold text-muted">
        {Icon && <Icon className={cn("size-3.5", tone === "clay" && "text-clay", tone === "leaf" && "text-leaf")} />}
        {label}
      </p>
      <p
        className={cn(
          "mt-3 font-display text-2xl font-semibold tracking-tight sm:text-3xl",
          tone === "clay" ? "text-clay" : tone === "leaf" ? "text-leaf" : "text-ink",
        )}
      >
        {value}
      </p>
    </Card>
  );
}

function TaskGroup({ title, icon: Icon, tone, orders, empty }: { title: string; icon: typeof AlarmClock; tone: string; orders: ReturnType<typeof todayStats>["late"]; empty: string }) {
  return (
    <section>
      <h2 className="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-muted">
        <Icon className={cn("size-4", tone)} />
        {title}
        <span className="rounded-full bg-surface-2 px-2 py-0.5 text-xs text-ink">{orders.length}</span>
      </h2>
      {orders.length ? (
        <div className="grid gap-2 md:grid-cols-2">
          {orders.map((o) => (
            <OrderRow key={o.id} order={o} />
          ))}
        </div>
      ) : (
        <p className="rounded-2xl border border-dashed border-line p-4 text-sm text-muted">{empty}</p>
      )}
    </section>
  );
}
