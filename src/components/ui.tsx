import Link from "next/link";
import type { ComponentProps, ReactNode } from "react";
import { cn, initials } from "@/lib/utils";

type Variant = "gold" | "ink" | "ghost" | "outline" | "light";
type Size = "sm" | "md" | "lg";

const variants: Record<Variant, string> = {
  gold: "bg-gold text-gold-ink shadow-[0_8px_24px_-8px_var(--gold)] hover:brightness-105 active:brightness-95",
  ink: "bg-ink text-bg hover:opacity-90",
  ghost: "text-ink hover:bg-surface-2",
  outline: "border border-line bg-surface text-ink hover:border-ink/30",
  light: "bg-white/10 text-white ring-1 ring-white/20 backdrop-blur hover:bg-white/15",
};
const sizes: Record<Size, string> = {
  sm: "h-9 px-3.5 text-sm gap-1.5",
  md: "h-11 px-5 text-[0.95rem] gap-2",
  lg: "h-13 px-6 text-base gap-2.5",
};

export function buttonClass(variant: Variant = "gold", size: Size = "md", className?: string) {
  return cn(
    "inline-flex shrink-0 items-center justify-center rounded-full font-semibold transition-all duration-200 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50",
    variants[variant],
    sizes[size],
    className,
  );
}

export function ButtonLink({
  variant,
  size,
  className,
  ...props
}: ComponentProps<typeof Link> & { variant?: Variant; size?: Size }) {
  return <Link className={buttonClass(variant, size, className)} {...props} />;
}

export function Button({
  variant,
  size,
  className,
  ...props
}: ComponentProps<"button"> & { variant?: Variant; size?: Size }) {
  return <button className={buttonClass(variant, size, className)} {...props} />;
}

type Tone = "gold" | "clay" | "leaf" | "sky" | "neutral";
const tones: Record<Tone, string> = {
  gold: "bg-gold",
  clay: "bg-clay",
  leaf: "bg-leaf",
  sky: "bg-sky",
  neutral: "bg-muted",
};

/** Statut sobre : un point de couleur et le texte, sans pastille. */
export function Badge({ tone = "neutral", className, children }: { tone?: Tone; className?: string; children: ReactNode }) {
  return (
    <span className={cn("inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-semibold text-ink-soft", className)}>
      <span className={cn("size-1.5 rounded-full", tones[tone])} aria-hidden />
      {children}
    </span>
  );
}

export function Card({ className, ...props }: ComponentProps<"div">) {
  return <div className={cn("rounded-2xl border border-line bg-surface", className)} {...props} />;
}

/** Couleurs des étoffes de la marque (wax, bogolan, kente, bazin, indigo). */
const avatarHues = ["#c8461f", "#1b2f6b", "#3b2516", "#14532d", "#b07d12", "#24346e", "#9f1d16"];

export function Avatar({ name, size = "md", className }: { name: string; size?: "sm" | "md" | "lg"; className?: string }) {
  const hue = avatarHues[[...name].reduce((sum, c) => sum + c.charCodeAt(0), 0) % avatarHues.length];
  return (
    <span
      className={cn(
        "inline-grid shrink-0 place-items-center rounded-full font-semibold tracking-wide text-white",
        size === "sm" && "size-8 text-[0.7rem]",
        size === "md" && "size-10 text-sm",
        size === "lg" && "size-16 text-xl",
        className,
      )}
      style={{ background: hue }}
      aria-hidden
    >
      {initials(name)}
    </span>
  );
}

export function SectionEyebrow({ children, className }: { children: ReactNode; className?: string }) {
  return (
    <p className={cn("inline-flex items-center gap-2 font-mono text-xs uppercase tracking-[0.14em] text-clay", className)}>
      {children}
    </p>
  );
}

export function Progress({ value, className }: { value: number; className?: string }) {
  return (
    <div className={cn("h-1.5 overflow-hidden rounded-full bg-surface-3", className)}>
      <div className="h-full rounded-full bg-gold transition-all" style={{ width: `${Math.min(100, Math.max(0, value))}%` }} />
    </div>
  );
}
