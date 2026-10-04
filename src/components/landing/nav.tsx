"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";

const links = [
  { href: "#histoire", label: "Comment ça marche" },
  { href: "#atelier", label: "Fonctionnalités" },
  { href: "#tarifs", label: "Tarifs" },
  { href: "#questions", label: "Questions" },
];

/**
 * Navigation en « mix-blend-difference » : lisible sur le hero sombre comme sur les sections claires,
 * sans changer de couleur.
 */
export function Nav() {
  const [open, setOpen] = useState(false);
  const [hidden, setHidden] = useState(false);

  // se cache quand on descend, revient quand on remonte
  useEffect(() => {
    let last = window.scrollY;
    const onScroll = () => {
      const y = window.scrollY;
      if (Math.abs(y - last) < 6) return;
      setHidden(y > last && y > 120);
      last = y;
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.documentElement.style.overflow = open ? "hidden" : "";
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && setOpen(false);
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [open]);

  return (
    <>
      <header
        className={cn(
          "pointer-events-none fixed inset-x-0 top-0 z-50 text-white mix-blend-difference transition-transform duration-500 ease-[cubic-bezier(.2,.7,.2,1)]",
          hidden && !open && "-translate-y-full",
        )}
      >
        <nav className="pointer-events-auto mx-auto flex h-16 max-w-[90rem] items-center justify-between px-4 sm:h-20 sm:px-8">
          <Link href="/" className="flex items-center gap-2.5" aria-label="Mansa Vibes, accueil">
            <svg viewBox="0 0 64 64" className="size-8" aria-hidden>
              <rect x="2" y="2" width="60" height="60" rx="15" fill="none" stroke="currentColor" strokeWidth="3" />
              <path d="M14 46V22l9 9 9-13 9 13 9-9v24" fill="none" stroke="currentColor" strokeWidth="4.5" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            <span className="font-display text-xl font-semibold tracking-tight">Mansa Vibes</span>
          </Link>
          <ul className="hidden items-center gap-8 font-mono text-[0.72rem] uppercase tracking-[0.14em] lg:flex">
            {links.map((l) => (
              <li key={l.href}>
                <a href={l.href} className="relative after:absolute after:-bottom-1 after:left-0 after:h-px after:w-full after:origin-right after:scale-x-0 after:bg-current after:transition-transform hover:after:origin-left hover:after:scale-x-100">
                  {l.label}
                </a>
              </li>
            ))}
          </ul>
          <div className="flex items-center gap-5 font-mono text-[0.72rem] uppercase tracking-[0.14em]">
            <Link href="/connexion" className="hidden sm:block">
              Connexion
            </Link>
            <Link href="/inscription" className="hidden rounded-full border border-current px-4 py-2 transition hover:bg-white hover:text-black sm:block">
              Essai gratuit
            </Link>
            <button type="button" onClick={() => setOpen(true)} className="lg:hidden" aria-label="Ouvrir le menu" aria-expanded={open}>
              Menu
            </button>
          </div>
        </nav>
      </header>

      {/* menu plein écran — mobile */}
      <div
        className={cn(
          "fixed inset-0 z-[60] flex flex-col bg-night px-4 pb-8 pt-4 text-[#f6ead2] transition-[clip-path] duration-700 ease-[cubic-bezier(.77,0,.18,1)] sm:px-8 lg:hidden",
          open ? "[clip-path:inset(0_0_0_0)]" : "pointer-events-none [clip-path:inset(0_0_100%_0)]",
        )}
        aria-hidden={!open}
      >
        <div className="flex h-12 items-center justify-between font-mono text-[0.72rem] uppercase tracking-[0.14em]">
          <span>Menu</span>
          <button type="button" onClick={() => setOpen(false)} aria-label="Fermer le menu" tabIndex={open ? 0 : -1}>
            Fermer
          </button>
        </div>
        <ul className="mt-auto space-y-1">
          {links.map((l, i) => (
            <li key={l.href} className="overflow-hidden">
              <a
                href={l.href}
                onClick={() => setOpen(false)}
                tabIndex={open ? 0 : -1}
                className={cn("flex items-baseline gap-4 font-display text-5xl leading-tight transition-transform duration-700 ease-[cubic-bezier(.2,.7,.2,1)]", open ? "translate-y-0" : "translate-y-full")}
                style={{ transitionDelay: open ? `${150 + i * 60}ms` : "0ms" }}
              >
                <span className="font-mono text-xs opacity-50">0{i + 1}</span>
                {l.label}
              </a>
            </li>
          ))}
        </ul>
        <div className="mt-10 grid grid-cols-2 gap-3 font-mono text-[0.72rem] uppercase tracking-[0.14em]">
          <Link href="/connexion" tabIndex={open ? 0 : -1} className="rounded-full border border-current/30 py-4 text-center">
            Connexion
          </Link>
          <Link href="/inscription" tabIndex={open ? 0 : -1} className="rounded-full bg-gold py-4 text-center text-gold-ink">
            Essai gratuit
          </Link>
        </div>
      </div>
    </>
  );
}
