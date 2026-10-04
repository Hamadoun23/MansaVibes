"use client";

import { ChevronRight, Search } from "lucide-react";
import Link from "next/link";
import { useState } from "react";
import { Avatar } from "@/components/ui";
import { clients, ordersOf } from "@/lib/demo";
import { fcfa } from "@/lib/utils";

export function ClientsList({ initialQuery = "" }: { initialQuery?: string }) {
  const [query, setQuery] = useState(initialQuery);
  const q = query.trim().toLowerCase();
  const list = clients
    .filter((c) => !q || `${c.name} ${c.phone} ${c.city}`.toLowerCase().includes(q))
    .sort((a, b) => a.name.localeCompare(b.name, "fr"));

  // regroupement alphabétique, façon répertoire de téléphone
  const groups = list.reduce<Record<string, typeof list>>((acc, c) => {
    const letter = c.name[0]!.toUpperCase();
    (acc[letter] ??= []).push(c);
    return acc;
  }, {});

  return (
    <div>
      <label className="relative block">
        <Search className="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-muted" />
        <input
          type="search"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Nom, téléphone, quartier…"
          className="h-12 w-full rounded-2xl border border-line bg-surface pl-11 pr-4 text-base text-ink outline-none transition placeholder:text-muted focus:border-gold focus:ring-4 focus:ring-gold/20"
        />
      </label>

      <div className="mt-5 space-y-5">
        {Object.entries(groups).map(([letter, items]) => (
          <section key={letter}>
            <p className="mb-2 px-1 font-display text-lg font-semibold text-gold">{letter}</p>
            <ul className="divide-y divide-line overflow-hidden rounded-3xl border border-line bg-surface">
              {items.map((c) => {
                const os = ordersOf(c.id);
                const due = os.reduce((s, o) => s + (o.price - o.paid), 0);
                const active = os.filter((o) => o.status !== "livree").length;
                return (
                  <li key={c.id}>
                    <Link href={`/app/clients/${c.id}`} className="flex items-center gap-3 p-3.5 transition hover:bg-surface-2/60 sm:p-4">
                      <Avatar name={c.name} />
                      <div className="min-w-0 flex-1">
                        <p className="truncate font-bold text-ink">{c.name}</p>
                        <p className="truncate text-sm text-muted">
                          {c.phone} · {c.city}
                        </p>
                      </div>
                      <div className="text-right text-xs font-semibold">
                        {active > 0 && <p className="text-ink">{active} en cours</p>}
                        {due > 0 && <p className="text-clay">doit {fcfa(due)}</p>}
                      </div>
                      <ChevronRight className="size-4 text-muted" />
                    </Link>
                  </li>
                );
              })}
            </ul>
          </section>
        ))}
        {list.length === 0 && <p className="rounded-3xl border border-dashed border-line p-10 text-center text-muted">Aucun client trouvé.</p>}
      </div>
    </div>
  );
}
