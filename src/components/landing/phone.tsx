import { AlarmClock, Bell, CheckCircle2, Mic, Wallet } from "lucide-react";
import { Avatar } from "@/components/ui";
import { cn } from "@/lib/utils";

/** Cadre de téléphone en CSS pur. */
export function PhoneFrame({ children, className }: { children: React.ReactNode; className?: string }) {
  return (
    <div className={cn("relative w-[17.5rem] rounded-[3rem] bg-night p-2.5 shadow-[0_40px_80px_-30px_rgb(20_17_42/0.6)] ring-1 ring-white/10 sm:w-[19rem]", className)}>
      <div className="absolute left-1/2 top-4 z-20 h-6 w-24 -translate-x-1/2 rounded-full bg-black" />
      <div className="relative overflow-hidden rounded-[2.4rem] bg-bg">{children}</div>
    </div>
  );
}

/** Écran « Aujourd'hui » miniature, fidèle à l'application. */
export function PhoneTodayScreen() {
  return (
    <div className="flex h-[36rem] flex-col text-ink sm:h-[39rem]">
      <div className="flex items-center justify-between px-5 pb-2 pt-12">
        <div>
          <p className="text-[0.65rem] font-semibold uppercase tracking-widest text-muted">Samedi</p>
          <p className="font-display text-xl font-semibold">Bonjour Awa</p>
        </div>
        <span className="relative grid size-9 place-items-center rounded-full bg-surface-2">
          <Bell className="size-4" />
          <span className="absolute right-2 top-2 size-1.5 rounded-full bg-clay" />
        </span>
      </div>

      <div className="mx-4 mt-2 rounded-2xl bg-night p-4 text-white">
        <p className="text-[0.65rem] font-semibold uppercase tracking-widest text-white/60">Encaissé aujourd&apos;hui</p>
        <p className="mt-1 font-display text-3xl font-semibold">
          90 000 <span className="text-base text-gold">F</span>
        </p>
        <div className="mt-3 flex items-end gap-1.5">
          {[40, 28, 62, 35, 80, 96, 58].map((h, i) => (
            <span key={i} className={cn("w-full rounded-sm", i === 6 ? "bg-gold" : "bg-white/15")} style={{ height: `${h * 0.4}px` }} />
          ))}
        </div>
      </div>

      <div className="mx-4 mt-3 grid grid-cols-3 gap-2 text-center">
        {[
          { n: 2, l: "En retard", c: "text-clay" },
          { n: 2, l: "À livrer", c: "text-ink" },
          { n: 2, l: "Prêtes", c: "text-leaf" },
        ].map((s) => (
          <div key={s.l} className="rounded-xl bg-surface p-2 ring-1 ring-line">
            <p className={cn("font-display text-xl font-semibold", s.c)}>{s.n}</p>
            <p className="text-[0.6rem] font-semibold text-muted">{s.l}</p>
          </div>
        ))}
      </div>

      <p className="mx-5 mt-4 text-[0.65rem] font-bold uppercase tracking-widest text-muted">À faire</p>
      <div className="mx-4 mt-2 space-y-2">
        {[
          { n: "Aminata Diop", g: "Boubou brodé", t: "Aujourd'hui", icon: AlarmClock, c: "text-clay" },
          { n: "Khady Ndiaye", g: "Ensemble wax", t: "Prête", icon: CheckCircle2, c: "text-leaf" },
          { n: "Cheikh Fall", g: "Grand boubou", t: "Retard 2 j", icon: AlarmClock, c: "text-clay" },
        ].map((row) => (
          <div key={row.n} className="flex items-center gap-2.5 rounded-xl bg-surface p-2.5 ring-1 ring-line">
            <Avatar name={row.n} size="sm" />
            <div className="min-w-0 flex-1">
              <p className="truncate text-xs font-bold">{row.n}</p>
              <p className="truncate text-[0.65rem] text-muted">{row.g}</p>
            </div>
            <span className={cn("flex items-center gap-1 text-[0.6rem] font-bold", row.c)}>
              <row.icon className="size-3" />
              {row.t}
            </span>
          </div>
        ))}
      </div>

      <div className="mt-auto flex items-center justify-around border-t border-line bg-surface px-4 pb-5 pt-2">
        <Wallet className="size-5 text-muted" />
        <span className="-mt-7 grid size-14 place-items-center rounded-full bg-gold text-gold-ink shadow-lg ring-4 ring-bg">
          <Mic className="size-6" />
        </span>
        <Bell className="size-5 text-muted" />
      </div>
    </div>
  );
}
