import Link from "next/link";
import { cn } from "@/lib/utils";

/** Monogramme : un « M » en couronne — Mansa, le roi. */
export function LogoMark({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" aria-hidden className={cn("size-9", className)}>
      <rect width="64" height="64" rx="16" fill="var(--night)" />
      <path
        d="M14 46V22l9 9 9-13 9 13 9-9v24"
        fill="none"
        stroke="var(--gold)"
        strokeWidth="5"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <circle cx="32" cy="12" r="3" fill="var(--gold)" />
    </svg>
  );
}

export function Logo({ href = "/", className, light }: { href?: string; className?: string; light?: boolean }) {
  return (
    <Link href={href} className={cn("group inline-flex items-center gap-2.5", className)} aria-label="Mansa Vibes, accueil">
      <LogoMark className="transition-transform duration-300 group-hover:-rotate-6" />
      <span className={cn("font-display text-[1.35rem] font-semibold tracking-tight", light ? "text-white" : "text-ink")}>
        Mansa Vibes
      </span>
    </Link>
  );
}

/** Motif bogolan stylisé, pour les fonds. */
export function Bogolan({ className, id = "bogolan" }: { className?: string; id?: string }) {
  return (
    <svg aria-hidden className={cn("pointer-events-none absolute inset-0 h-full w-full", className)}>
      <defs>
        <pattern id={id} width="56" height="56" patternUnits="userSpaceOnUse">
          <path d="M0 28h56M28 0v56" stroke="currentColor" strokeWidth="1" strokeDasharray="2 6" />
          <path d="M8 8l12 12M48 8L36 20M8 48l12-12M48 48L36 36" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" />
          <circle cx="28" cy="28" r="3.2" fill="none" stroke="currentColor" strokeWidth="1.2" />
          <circle cx="0" cy="0" r="1.6" fill="currentColor" />
          <circle cx="56" cy="0" r="1.6" fill="currentColor" />
          <circle cx="0" cy="56" r="1.6" fill="currentColor" />
          <circle cx="56" cy="56" r="1.6" fill="currentColor" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill={`url(#${id})`} />
    </svg>
  );
}
