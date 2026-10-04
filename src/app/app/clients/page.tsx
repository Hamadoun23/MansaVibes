import { UserPlus } from "lucide-react";
import type { Metadata } from "next";
import { ClientsList } from "@/components/app/clients-list";
import { PageBody, PageHeader } from "@/components/app/shell";
import { ButtonLink } from "@/components/ui";
import { clients } from "@/lib/demo";

export const metadata: Metadata = { title: "Clients" };

export default function ClientsPage() {
  return (
    <>
      <PageHeader
        subtitle={`${clients.length} clients`}
        title="Clients"
        action={
          <ButtonLink href="/app/assistant" size="sm" className="size-10 px-0 sm:w-auto sm:px-4">
            <UserPlus className="size-4" />
            <span className="hidden sm:inline">Nouveau client</span>
          </ButtonLink>
        }
      />
      <PageBody>
        <ClientsList />
      </PageBody>
    </>
  );
}
