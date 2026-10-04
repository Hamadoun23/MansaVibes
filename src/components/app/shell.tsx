"use client";

import { ClipboardList, Home, LogOut, Mic, Plus, Users, Wallet } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import type { ReactNode } from "react";
import { Logo } from "@/components/brand";
import { Avatar, ButtonLink } from "@/components/ui";
import { atelier } from "@/lib/demo";
import { cn } from "@/lib/utils";

const nav = [
  { href: "/app", label: "Aujourd'hui", icon: Home },
  { href: "/app/commandes", label: "Commandes", icon: ClipboardList },
  { href: "/app/clients", label: "Clients", icon: Users },
  { href: "/app/caisse", label: "Caisse", icon: Wallet },
];

function isActive(pathname: string, href: string) {
  return href === "/app" ? pathname === "/app" : pathname.startsWith(href);
}

export function AppShell({ children }: { children: ReactNode }) {
  const pathname = usePathname();

  return (
    <div className="min-h-dvh lg:grid lg:grid-cols-[16.5rem_1fr]">
      {/* barre latérale — ordinateur */}
      <aside className="sticky top-0 hidden h-dvh flex-col border-r border-line bg-surface/60 p-4 lg:flex">
        <Logo href="/app" className="px-2 py-1" />
        <div className="mt-6 rounded-2xl bg-surface-2 p-3">
          <p className="text-[0.65rem] font-bold uppercase tracking-widest text-muted">Atelier</p>
          <p className="mt-0.5 truncate text-sm font-bold text-ink">{atelier.name}</p>
          <p className="truncate text-xs text-muted">Plan {atelier.plan} · essai 11 j</p>
        </div>

        <ButtonLink href="/app/assistant" className="mt-5 w-full justify-start">
          <Mic className="size-4" /> Dicter une commande
        </ButtonLink>

        <nav className="mt-5 space-y-1">
          {nav.map((item) => {
            const active = isActive(pathname, item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                className={cn(
                  "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition",
                  active ? "bg-ink text-bg" : "text-ink-soft hover:bg-surface-2 hover:text-ink",
                )}
              >
                <item.icon className="size-[1.1rem]" />
                {item.label}
              </Link>
            );
          })}
        </nav>

        <div className="mt-auto flex items-center gap-3 rounded-2xl border border-line p-2.5">
          <Avatar name={atelier.owner} size="sm" />
          <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-bold text-ink">{atelier.owner}</p>
            <p className="text-xs text-muted">Gérante</p>
          </div>
          <Link href="/" className="grid size-8 place-items-center rounded-lg text-muted hover:bg-surface-2 hover:text-ink" aria-label="Quitter la démo">
            <LogOut className="size-4" />
          </Link>
        </div>
      </aside>

      <div className="min-w-0 pb-28 lg:pb-0">{children}</div>

      {/* barre d'onglets — mobile */}
      <nav className="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface/90 px-2 pt-1.5 backdrop-blur-xl safe-bottom lg:hidden" aria-label="Navigation principale">
        <ul className="mx-auto grid max-w-md grid-cols-5 items-end">
          {nav.slice(0, 2).map((item) => (
            <TabItem key={item.href} {...item} active={isActive(pathname, item.href)} />
          ))}
          <li className="flex justify-center">
            <Link
              href="/app/assistant"
              aria-label="Assistant vocal"
              className={cn(
                "-mt-7 grid size-16 place-items-center rounded-full bg-gold text-gold-ink shadow-[0_10px_30px_-8px_var(--gold)] ring-[5px] ring-bg transition active:scale-95",
                pathname === "/app/assistant" && "bg-ink text-gold",
              )}
            >
              <Mic className="size-6" />
            </Link>
          </li>
          {nav.slice(2).map((item) => (
            <TabItem key={item.href} {...item} active={isActive(pathname, item.href)} />
          ))}
        </ul>
      </nav>
    </div>
  );
}

function TabItem({ href, label, icon: Icon, active }: { href: string; label: string; icon: typeof Home; active: boolean }) {
  return (
    <li>
      <Link href={href} className={cn("flex flex-col items-center gap-1 rounded-xl py-1.5 text-[0.68rem] font-bold transition", active ? "text-ink" : "text-muted")}>
        <span className={cn("grid h-7 w-12 place-items-center rounded-full transition", active && "bg-gold-soft")}>
          <Icon className="size-5" />
        </span>
        {label}
      </Link>
    </li>
  );
}

/** En-tête de page de l'application. */
export function PageHeader({ title, subtitle, action, back }: { title: ReactNode; subtitle?: ReactNode; action?: ReactNode; back?: ReactNode }) {
  return (
    <header className="sticky top-0 z-30 border-b border-line/60 bg-bg/85 backdrop-blur-xl">
      <div className="mx-auto flex max-w-5xl items-center gap-3 px-4 pb-3 pt-[max(1rem,env(safe-area-inset-top))] sm:px-6 lg:px-8 lg:pt-6">
        {back}
        <div className="min-w-0 flex-1">
          {subtitle && <p className="truncate text-xs font-bold uppercase tracking-widest text-muted">{subtitle}</p>}
          <h1 className="truncate font-display text-[1.75rem] font-semibold leading-tight tracking-tight text-ink sm:text-3xl">{title}</h1>
        </div>
        {action}
      </div>
    </header>
  );
}

export function PageBody({ children, className }: { children: ReactNode; className?: string }) {
  return <div className={cn("mx-auto max-w-5xl px-4 py-5 sm:px-6 lg:px-8 lg:py-8", className)}>{children}</div>;
}

export function NewOrderButton() {
  return (
    <ButtonLink href="/app/assistant" size="sm" className="size-10 px-0 sm:w-auto sm:px-4">
      <Plus className="size-4" />
      <span className="hidden sm:inline">Nouvelle commande</span>
    </ButtonLink>
  );
}
