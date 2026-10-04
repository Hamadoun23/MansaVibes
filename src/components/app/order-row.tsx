import { ChevronRight } from "lucide-react";
import Link from "next/link";
import { Avatar, Badge } from "@/components/ui";
import { clientById, statusMeta, type Order } from "@/lib/demo";
import { cn, dueLabel, fcfa } from "@/lib/utils";

export function OrderRow({ order, className }: { order: Order; className?: string }) {
  const client = clientById(order.clientId)!;
  const meta = statusMeta[order.status];
  const rest = order.price - order.paid;
  const late = order.dueIn < 0 && order.status !== "prete" && order.status !== "livree";

  return (
    <Link
      href={`/app/commandes/${order.id}`}
      className={cn("group flex items-center gap-3 rounded-2xl border border-line bg-surface p-3 transition hover:border-ink/20 hover:shadow-md active:scale-[0.99] sm:p-4", className)}
    >
      <Avatar name={client.name} />
      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <p className="truncate font-bold text-ink">{client.name}</p>
          {order.urgent && <span className="size-1.5 shrink-0 rounded-full bg-clay" aria-label="urgent" />}
        </div>
        <p className="truncate text-sm text-muted">{order.garment}</p>
      </div>
      <div className="flex shrink-0 flex-col items-end gap-1">
        <Badge tone={meta.tone}>{meta.label}</Badge>
        <p className={cn("text-xs font-semibold", late ? "text-clay" : "text-muted")}>
          {order.status === "livree" ? "Livrée" : dueLabel(order.dueIn)}
          {rest > 0 && <span className="text-ink-soft"> · reste {fcfa(rest)}</span>}
        </p>
      </div>
      <ChevronRight className="hidden size-4 text-muted transition group-hover:translate-x-0.5 sm:block" />
    </Link>
  );
}
