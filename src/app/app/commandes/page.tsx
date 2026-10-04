import type { Metadata } from "next";
import { OrdersBoard } from "@/components/app/orders-board";
import { NewOrderButton, PageBody, PageHeader } from "@/components/app/shell";
import { orders } from "@/lib/demo";

export const metadata: Metadata = { title: "Commandes" };

export default function OrdersPage() {
  const active = orders.filter((o) => o.status !== "livree").length;
  return (
    <>
      <PageHeader subtitle={`${active} en cours`} title="Commandes" action={<NewOrderButton />} />
      <PageBody>
        <OrdersBoard />
      </PageBody>
    </>
  );
}
