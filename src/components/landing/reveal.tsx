"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { useRef, type ElementType, type ReactNode } from "react";
import { cn } from "@/lib/utils";

gsap.registerPlugin(ScrollTrigger, useGSAP);

/**
 * Titre révélé ligne par ligne (chaque ligne glisse hors d'un masque).
 * `lines` : une entrée par ligne visuelle.
 */
export function RevealLines({
  lines,
  as: Tag = "h2",
  className,
  lineClassName,
  immediate,
  delay = 0,
}: {
  lines: ReactNode[];
  as?: ElementType;
  className?: string;
  lineClassName?: string;
  immediate?: boolean;
  delay?: number;
}) {
  const ref = useRef<HTMLElement>(null);

  useGSAP(
    () => {
      const mm = gsap.matchMedia();
      mm.add("(prefers-reduced-motion: no-preference)", () => {
        gsap.from(".reveal-line", {
          yPercent: 110,
          rotate: 2,
          duration: 1.1,
          ease: "expo.out",
          stagger: 0.09,
          delay,
          scrollTrigger: immediate ? undefined : { trigger: ref.current, start: "top 85%", once: true },
        });
      });
    },
    { scope: ref },
  );

  return (
    <Tag ref={ref} className={className}>
      {lines.map((line, i) => (
        <span key={i} className="block overflow-hidden pb-[0.08em]">
          <span className={cn("reveal-line block origin-left", lineClassName)}>{line}</span>
        </span>
      ))}
    </Tag>
  );
}

/** Petit apparition en fondu quand l'élément entre à l'écran. */
export function FadeIn({ children, className, y = 24 }: { children: ReactNode; className?: string; y?: number }) {
  const ref = useRef<HTMLDivElement>(null);
  useGSAP(
    () => {
      const mm = gsap.matchMedia();
      mm.add("(prefers-reduced-motion: no-preference)", () => {
        gsap.from(ref.current, { y, opacity: 0, duration: 1, ease: "power3.out", scrollTrigger: { trigger: ref.current, start: "top 88%", once: true } });
      });
    },
    { scope: ref },
  );
  return (
    <div ref={ref} className={className}>
      {children}
    </div>
  );
}

/** Étiquette éditoriale : (01) Titre de section */
export function Label({ n, children, className }: { n?: string; children: ReactNode; className?: string }) {
  return (
    <p className={cn("font-mono text-[0.72rem] uppercase tracking-[0.14em]", className)}>
      {n && <span className="mr-3 opacity-50">({n})</span>}
      {children}
    </p>
  );
}
