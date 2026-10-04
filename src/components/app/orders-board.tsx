"use client";

import { LayoutGrid, List, Search } from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";
import { Avatar, Progress } from "@/components/ui";
import { clientById, orders, statusMeta, statusOrder, type OrderStatus } from "@/lib/demo";
import { cn, dueLabel, fcfa } from "@/lib/utils";
import { OrderRow } from "./order-row";

type Filter = "toutes" | "retard" | OrderStatus;

export function OrdersBoard({ initialQuery = "" }: { initialQuery?: string }) {
  const [filter, setFilter] = useState<Filter>("toutes");
  const [query, setQuery] = useState(initialQuery);
  const [view, setView] = useState<"liste" | "atelier">("liste");

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    return orders.filter((o) => {
      const client = clientById(o.clientId)!;
      if (q && !`${client.name} ${o.garment} ${o.ref} ${o.fabric}`.toLowerCase().includes(q)) return false;
      if (filter === "toutes") return o.status !== "livree";
      if (filter === "retard") return o.dueIn < 0 && o.status !== "prete" && o.status !== "livree";
      return o.status === filter;
    });
  }, [filter, query]);

  const chips: { v: Filter; l: string; n: number }[] = [
    { v: "toutes", l: "En cours", n: orders.filter((o) => o.status !== "livree").length },
    { v: "retard", l: "En retard", n: orders.filter((o) => o.dueIn < 0 && o.status !== "prete" && o.status !== "livree").length },
    ...statusOrder.map((s) => ({ v: s as Filter, l: statusMeta[s].label, n: orders.filter((o) => o.status === s).length })),
  ];

  return (
    <div>
      <div className="flex gap-2">
        <label className="relative flex-1">
          <Search className="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-muted" />
          <input
            type="search"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Client, tenue, tissu, n°…"
            className="h-12 w-full rounded-2xl border border-line bg-surface pl-11 pr-4 text-base text-ink outline-none transition placeholder:text-muted focus:border-gold focus:ring-4 focus:ring-gold/20"
          />
        </label>
        <div className="hidden rounded-2xl border border-line bg-surface p-1 md:flex">
          {(["liste", "atelier"] as const).map((v) => (
            <button
              key={v}
              type="button"
              onClick={() => setView(v)}
              className={cn("grid size-10 place-items-center rounded-xl transition", view === v ? "bg-ink text-bg" : "text-muted hover:text-ink")}
              aria-label={v === "liste" ? "Vue liste" : "Vue atelier"}
              aria-pressed={view === v}
            >
              {v === "liste" ? <List className="size-4" /> : <LayoutGrid className="size-4" />}
            </button>
          ))}
        </div>
      </div>

      <div className="no-scrollbar -mx-4 mt-3 flex gap-2 overflow-x-auto px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0">
        {chips.map((c) => (
          <button
            key={c.v}
            type="button"
            onClick={() => setFilter(c.v)}
            className={cn(
              "flex h-9 shrink-0 items-center gap-1.5 rounded-full border px-3.5 text-sm font-bold transition",
              filter === c.v ? "border-ink bg-ink text-bg" : "border-line bg-surface text-ink-soft hover:border-ink/30",
              c.v === "retard" && filter !== c.v && c.n > 0 && "text-clay",
            )}
          >
            {c.l}
            <span className={cn("text-xs", filter === c.v ? "text-bg/60" : "text-muted")}>{c.n}</span>
          </button>
        ))}
      </div>

      {view === "liste" || filter !== "toutes" ? (
        <div className="mt-5 grid gap-2">
          {filtered.length ? filtered.map((o) => <OrderRow key={o.id} order={o} />) : <Empty />}
        </div>
      ) : (
        <div className="mt-5 grid grid-cols-5 gap-3">
          {statusOrder.slice(0, 5).map((s) => {
            const col = filtered.filter((o) => o.status === s);
            return (
              <div key={s} className="rounded-2xl bg-surface-2 p-2">
                <p className="flex items-center justify-between px-2 py-1.5 text-xs font-bold uppercase tracking-wider text-ink-soft">
                  {statusMeta[s].label}
                  <span className="text-muted">{col.length}</span>
                </p>
                <div className="space-y-2">
                  {col.map((o) => {
                    const client = clientById(o.clientId)!;
                    return (
                      <Link key={o.id} href={`/app/commandes/${o.id}`} className="block rounded-2xl border border-line bg-surface p-3 transition hover:shadow-md">
                        <div className="flex items-center gap-2">
                          <Avatar name={client.name} size="sm" />
                          <p className="truncate text-sm font-bold text-ink">{client.name}</p>
                        </div>
                        <p className="mt-2 truncate text-xs text-muted">{o.garment}</p>
                        <Progress value={(o.paid / o.price) * 100} className="mt-2" />
                        <p className={cn("mt-2 text-[0.7rem] font-bold", o.dueIn < 0 && s !== "prete" ? "text-clay" : "text-muted")}>
                          {dueLabel(o.dueIn)} · {fcfa(o.price)}
                        </p>
                      </Link>
                    );
                  })}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

function Empty() {
  return <p className="rounded-2xl border border-dashed border-line p-10 text-center text-muted">Aucune commande ne correspond.</p>;
}
