"use client";

import { Menu, X } from "lucide-react";
import Link from "next/link";
import { useEffect, useState } from "react";
import { Logo } from "@/components/brand";
import { ButtonLink } from "@/components/ui";
import { cn } from "@/lib/utils";

const links = [
  { href: "#assistant", label: "Assistant vocal" },
  { href: "#fonctionnalites", label: "Fonctionnalités" },
  { href: "#tarifs", label: "Tarifs" },
  { href: "#faq", label: "FAQ" },
];

export function Nav() {
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = open ? "hidden" : "";
  }, [open]);

  return (
    <header className="fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-4">
      <nav
        className={cn(
          "mx-auto flex h-16 max-w-6xl items-center justify-between rounded-full px-3 pl-4 transition-all duration-300 sm:px-4 sm:pl-5",
          scrolled || open
            ? "border border-line bg-surface/80 shadow-[0_10px_40px_-20px_rgb(20_17_42/0.35)] backdrop-blur-xl"
            : "border border-transparent",
        )}
      >
        <Logo />
        <ul className="hidden items-center gap-1 lg:flex">
          {links.map((l) => (
            <li key={l.href}>
              <a href={l.href} className="rounded-full px-4 py-2 text-sm font-semibold text-ink-soft transition hover:bg-surface-2 hover:text-ink">
                {l.label}
              </a>
            </li>
          ))}
        </ul>
        <div className="flex items-center gap-2">
          <Link href="/connexion" className="hidden px-3 text-sm font-semibold text-ink-soft hover:text-ink sm:block">
            Connexion
          </Link>
          <ButtonLink href="/inscription" size="sm" className="hidden sm:inline-flex">
            Essai gratuit
          </ButtonLink>
          <button
            type="button"
            onClick={() => setOpen((v) => !v)}
            className="grid size-11 place-items-center rounded-full text-ink hover:bg-surface-2 lg:hidden"
            aria-label={open ? "Fermer le menu" : "Ouvrir le menu"}
            aria-expanded={open}
          >
            {open ? <X className="size-5" /> : <Menu className="size-5" />}
          </button>
        </div>
      </nav>

      <div
        className={cn(
          "mx-auto mt-2 max-w-6xl origin-top overflow-hidden rounded-[2rem] border border-line bg-surface/95 backdrop-blur-xl transition-all duration-300 lg:hidden",
          open ? "visible scale-100 opacity-100" : "invisible scale-95 opacity-0",
        )}
      >
        <ul className="p-3">
          {links.map((l) => (
            <li key={l.href}>
              <a href={l.href} onClick={() => setOpen(false)} className="flex items-center justify-between rounded-2xl px-4 py-4 font-display text-2xl text-ink hover:bg-surface-2">
                {l.label}
              </a>
            </li>
          ))}
        </ul>
        <div className="grid grid-cols-2 gap-2 border-t border-line p-3">
          <ButtonLink href="/connexion" variant="outline">Connexion</ButtonLink>
          <ButtonLink href="/inscription">Essai gratuit</ButtonLink>
        </div>
      </div>
    </header>
  );
}
