import type { Metadata } from "next";
import { OrdersBoard } from "@/components/app/orders-board";
import { NewOrderButton, PageBody, PageHeader } from "@/components/app/shell";
import { orders } from "@/lib/demo";

export const metadata: Metadata = { title: "Commandes" };

export default async function OrdersPage({ searchParams }: PageProps<"/app/commandes">) {
  const q = (await searchParams).q;
  const initialQuery = typeof q === "string" ? q : "";
  const active = orders.filter((o) => o.status !== "livree").length;
  return (
    <>
      <PageHeader subtitle={`${active} en cours`} title="Commandes" action={<NewOrderButton />} />
      <PageBody>
        <OrdersBoard key={initialQuery} initialQuery={initialQuery} />
      </PageBody>
    </>
  );
}
