"use client";

import { ArrowRight, Eye, EyeOff } from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState, type ReactNode } from "react";
import { Bogolan, Logo } from "@/components/brand";
import { Button } from "@/components/ui";

export function AuthShell({ title, subtitle, children, footer }: { title: ReactNode; subtitle: string; children: ReactNode; footer: ReactNode }) {
  return (
    <div className="grid min-h-dvh lg:grid-cols-2">
      <aside className="relative hidden overflow-hidden bg-night p-12 text-white grain lg:flex lg:flex-col">
        <Bogolan className="text-gold/[0.08]" id="auth-bogolan" />
        <div aria-hidden className="absolute -bottom-40 -left-20 size-[32rem] rounded-full bg-gold/25 blur-[120px]" />
        <Logo light className="relative" />
        <blockquote className="relative mt-auto max-w-md">
          <p className="font-display text-4xl leading-tight">
            « Je dicte pendant que je coupe. Le soir, <span className="italic text-gold">la caisse est juste.</span> »
          </p>
          <footer className="mt-6 text-sm text-white/60">Moussa Traoré — Maison Traoré, Bamako</footer>
        </blockquote>
      </aside>
      <main className="flex flex-col px-4 py-6 sm:px-8">
        <div className="lg:hidden">
          <Logo />
        </div>
        <div className="mx-auto my-auto w-full max-w-sm py-10">
          <h1 className="font-display text-4xl font-medium tracking-tight text-ink">{title}</h1>
          <p className="mt-2 text-ink-soft">{subtitle}</p>
          <div className="mt-8">{children}</div>
          <p className="mt-8 text-center text-sm text-muted">{footer}</p>
        </div>
      </main>
    </div>
  );
}

export function Field({ label, hint, ...props }: React.ComponentProps<"input"> & { label: string; hint?: string }) {
  return (
    <label className="block">
      <span className="text-sm font-bold text-ink">{label}</span>
      <input
        {...props}
        className="mt-1.5 h-12 w-full rounded-2xl border border-line bg-surface px-4 text-base text-ink outline-none transition placeholder:text-muted/70 focus:border-gold focus:ring-4 focus:ring-gold/20"
      />
      {hint && <span className="mt-1 block text-xs text-muted">{hint}</span>}
    </label>
  );
}

function PasswordField({ label }: { label: string }) {
  const [show, setShow] = useState(false);
  return (
    <label className="block">
      <span className="text-sm font-bold text-ink">{label}</span>
      <span className="relative mt-1.5 block">
        <input
          type={show ? "text" : "password"}
          required
          minLength={8}
          autoComplete="current-password"
          className="h-12 w-full rounded-2xl border border-line bg-surface px-4 pr-12 text-base text-ink outline-none transition focus:border-gold focus:ring-4 focus:ring-gold/20"
        />
        <button type="button" onClick={() => setShow((s) => !s)} className="absolute right-1.5 top-1.5 grid size-9 place-items-center rounded-xl text-muted hover:text-ink" aria-label={show ? "Masquer" : "Afficher"}>
          {show ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
        </button>
      </span>
    </label>
  );
}

/** Formulaires de démonstration : ils mènent à l'application en attendant l'API d'authentification. */
export function LoginForm() {
  const router = useRouter();
  return (
    <form
      className="space-y-4"
      onSubmit={(e) => {
        e.preventDefault();
        router.push("/app");
      }}
    >
      <Field label="Téléphone" type="tel" inputMode="tel" autoComplete="tel" placeholder="77 123 45 67" required />
      <PasswordField label="Mot de passe" />
      <div className="text-right">
        <Link href="#" className="text-sm font-semibold text-clay hover:underline">
          Mot de passe oublié ?
        </Link>
      </div>
      <Button type="submit" size="lg" className="w-full">
        Se connecter <ArrowRight className="size-4" />
      </Button>
    </form>
  );
}

export function SignupForm() {
  const router = useRouter();
  return (
    <form
      className="space-y-4"
      onSubmit={(e) => {
        e.preventDefault();
        router.push("/app");
      }}
    >
      <Field label="Nom de l'atelier" placeholder="Atelier Awa Couture" required />
      <Field label="Votre nom" autoComplete="name" placeholder="Awa Ndiaye" required />
      <Field label="Téléphone WhatsApp" type="tel" inputMode="tel" autoComplete="tel" placeholder="77 123 45 67" hint="Vos clients recevront les messages depuis ce numéro." required />
      <PasswordField label="Mot de passe" />
      <Button type="submit" size="lg" className="w-full">
        Créer mon atelier <ArrowRight className="size-4" />
      </Button>
      <p className="text-center text-xs text-muted">14 jours d&apos;essai gratuit, sans carte bancaire.</p>
    </form>
  );
}
