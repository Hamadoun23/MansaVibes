import Link from "next/link";
import type { ReactNode } from "react";
import { Swatch, type Fabric } from "@/components/landing/swatch";

/** Écran de repli commun (erreur, page introuvable) : une étoffe, un titre, des actions. */
export function FallbackScreen({ fabric, code, title, text, actions }: { fabric: Fabric; code: string; title: ReactNode; text: string; actions: ReactNode }) {
  return (
    <div className="theme-light grid min-h-[70dvh] place-items-center bg-bg px-4 py-16 text-ink">
      <div className="w-full max-w-md text-center">
        <div className="mx-auto h-28 w-24 rotate-[-4deg] overflow-hidden rounded-[3px] shadow-[0_20px_40px_-18px_rgba(20,17,42,0.5)]">
          <Swatch fabric={fabric} />
        </div>
        <p className="mt-8 font-mono text-[0.72rem] uppercase tracking-[0.14em] text-muted">{code}</p>
        <h1 className="mt-3 font-display text-4xl font-light leading-tight tracking-tight">{title}</h1>
        <p className="mt-4 text-ink-soft">{text}</p>
        <div className="mt-8 flex flex-wrap justify-center gap-3">{actions}</div>
      </div>
    </div>
  );
}

export const fallbackPrimary = "inline-flex h-12 items-center rounded-full bg-ink px-6 font-semibold text-bg transition hover:opacity-90";
export const fallbackSecondary = "inline-flex h-12 items-center rounded-full border border-ink/20 px-6 font-semibold text-ink transition hover:border-ink";

export function HomeLink({ href = "/", children = "Retour à l'accueil" }: { href?: string; children?: ReactNode }) {
  return (
    <Link href={href} className={fallbackSecondary}>
      {children}
    </Link>
  );
}
