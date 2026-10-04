import type { Metadata } from "next";
import Link from "next/link";
import { AuthShell, LoginForm } from "@/components/auth";

export const metadata: Metadata = { title: "Connexion" };

export default function Page() {
  return (
    <AuthShell
      title={<>Bon retour <span className="italic text-clay">à l&apos;atelier.</span></>}
      subtitle="Connectez-vous avec votre numéro de téléphone."
      footer={
        <>
          Pas encore de compte ?{" "}
          <Link href="/inscription" className="font-bold text-ink underline decoration-gold decoration-2 underline-offset-4">
            Essai gratuit
          </Link>
        </>
      }
    >
      <LoginForm />
    </AuthShell>
  );
}
