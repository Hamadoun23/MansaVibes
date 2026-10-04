import type { Metadata } from "next";
import Link from "next/link";
import { AuthShell, SignupForm } from "@/components/auth";

export const metadata: Metadata = { title: "Créer mon atelier" };

export default function Page() {
  return (
    <AuthShell
      title={<>Ouvrez votre <span className="italic text-clay">atelier.</span></>}
      subtitle="Deux minutes, et vos premiers clients sont dedans."
      footer={
        <>
          Déjà un compte ?{" "}
          <Link href="/connexion" className="font-bold text-ink underline decoration-gold decoration-2 underline-offset-4">
            Connexion
          </Link>
        </>
      }
    >
      <SignupForm />
    </AuthShell>
  );
}
