import Link from "next/link";
import type { ReactNode } from "react";
import { Bogolan, LogoMark } from "@/components/brand";

/** Écran de repli commun (erreur, page introuvable). */
export function FallbackScreen({ code, title, text, actions }: { code: string; title: ReactNode; text: string; actions: ReactNode }) {
  return (
    <div className="relative grid min-h-[70dvh] place-items-center overflow-hidden px-4 py-16">
      <Bogolan className="text-ink/[0.04] [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]" id="fallback-bogolan" />
      <div className="relative w-full max-w-md text-center">
        <LogoMark className="mx-auto size-14" />
        <p className="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-clay">{code}</p>
        <h1 className="mt-3 font-display text-4xl font-medium leading-tight tracking-tight text-ink">{title}</h1>
        <p className="mt-4 text-ink-soft">{text}</p>
        <div className="mt-8 flex flex-wrap justify-center gap-3">{actions}</div>
      </div>
    </div>
  );
}

export function HomeLink({ href = "/", children = "Retour à l'accueil" }: { href?: string; children?: ReactNode }) {
  return (
    <Link href={href} className="inline-flex h-11 items-center rounded-full border border-line bg-surface px-5 font-semibold text-ink transition hover:border-ink/30">
      {children}
    </Link>
  );
}
