import type { Metadata } from "next";
import { Assistant } from "@/components/app/assistant";
import { PageBody, PageHeader } from "@/components/app/shell";

export const metadata: Metadata = { title: "Assistant vocal" };

export default function AssistantPage() {
  return (
    <>
      <PageHeader subtitle="Assistant" title="Dicter une commande" />
      <PageBody>
        <Assistant />
      </PageBody>
    </>
  );
}
