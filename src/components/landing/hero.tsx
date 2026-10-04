"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import Link from "next/link";
import { useRef } from "react";
import { Label, RevealLines } from "./reveal";
import { Swatch, fabricNames, type Fabric } from "./swatch";

gsap.registerPlugin(ScrollTrigger, useGSAP);

/** Échantillons épinglés comme sur le mur d'un atelier : position, rotation, vitesse de parallaxe. */
const pinned: { fabric: Fabric; x: string; y: string; r: number; speed: number; w: string }[] = [
  { fabric: "bogolan", x: "4%", y: "10%", r: -8, speed: 0.6, w: "38%" },
  { fabric: "wax", x: "38%", y: "0%", r: 5, speed: 1.1, w: "40%" },
  { fabric: "kente", x: "62%", y: "30%", r: -4, speed: 0.8, w: "34%" },
  { fabric: "indigo", x: "18%", y: "46%", r: 7, speed: 1.4, w: "36%" },
  { fabric: "bazin", x: "52%", y: "58%", r: -6, speed: 1, w: "38%" },
];

export function Hero() {
  const ref = useRef<HTMLElement>(null);

  useGSAP(
    () => {
      const mm = gsap.matchMedia();
      mm.add("(prefers-reduced-motion: no-preference)", () => {
        // arrivée : les échantillons tombent sur le mur
        gsap.from(".hero-swatch", {
          y: 120,
          opacity: 0,
          rotate: (i) => pinned[i].r * 3,
          duration: 1.4,
          ease: "expo.out",
          stagger: 0.08,
          delay: 0.35,
        });
        gsap.from(".hero-fade", { opacity: 0, y: 16, duration: 1, ease: "power3.out", stagger: 0.1, delay: 0.7 });

        // au scroll : parallaxe et dispersion des échantillons, le titre s'éloigne
        const tl = gsap.timeline({ scrollTrigger: { trigger: ref.current, start: "top top", end: "bottom top", scrub: 0.6 } });
        gsap.utils.toArray<HTMLElement>(".hero-swatch").forEach((el, i) => {
          tl.to(el, { yPercent: -60 * pinned[i].speed, rotate: pinned[i].r * 2.2, ease: "none" }, 0);
        });
        tl.to(".hero-title", { yPercent: 18, opacity: 0.25, ease: "none" }, 0);
      });
    },
    { scope: ref },
  );

  return (
    <section ref={ref} className="relative flex min-h-svh flex-col overflow-hidden bg-night pb-8 pt-28 text-[#f6ead2] sm:pt-32">
      <div className="mx-auto grid w-full max-w-[90rem] flex-1 gap-10 px-4 sm:px-8 lg:grid-cols-[1.25fr_1fr] lg:gap-6">
        <div className="flex flex-col">
          <Label className="hero-fade text-[#f6ead2]/60">Logiciel pour ateliers de couture — Dakar · Bamako · Abidjan</Label>
          <RevealLines
            as="h1"
            immediate
            delay={0.1}
            className="hero-title mt-8 font-display text-[clamp(3.6rem,12.5vw,11.5rem)] font-light leading-[0.86] tracking-[-0.045em]"
            lines={[
              "L'atelier,",
              <>
                sans le <em className="font-normal italic text-gold">cahier.</em>
              </>,
            ]}
          />
          <div className="mt-auto grid gap-8 pt-12 sm:grid-cols-[1fr_auto] sm:items-end">
            <p className="hero-fade max-w-md text-lg leading-relaxed text-[#f6ead2]/70">
              Mesures, commandes, acomptes Wave et Orange Money, messages WhatsApp. Dictez, Mansa Vibes remplit — depuis votre téléphone,
              même quand le réseau est faible.
            </p>
            <div className="hero-fade flex flex-wrap gap-3">
              <Link
                href="/inscription"
                className="group inline-flex h-14 items-center gap-3 rounded-full bg-gold pl-6 pr-2 font-semibold text-gold-ink transition hover:bg-[#f6ead2]"
              >
                Ouvrir mon atelier
                <span className="grid size-10 place-items-center rounded-full bg-night text-gold transition-transform group-hover:rotate-[-45deg]">→</span>
              </Link>
              <Link href="/app" className="inline-flex h-14 items-center rounded-full border border-[#f6ead2]/25 px-6 font-semibold transition hover:border-[#f6ead2]">
                Voir la démo
              </Link>
            </div>
          </div>
        </div>

        {/* mur d'échantillons */}
        <div className="relative h-[24rem] sm:h-[30rem] lg:h-auto" aria-hidden>
          {pinned.map((s) => (
            <figure
              key={s.fabric}
              className="hero-swatch absolute aspect-[4/5] overflow-hidden rounded-[3px] shadow-[0_30px_60px_-20px_rgba(0,0,0,0.6)]"
              style={{ left: s.x, top: s.y, width: s.w, rotate: `${s.r}deg` }}
            >
              <Swatch fabric={s.fabric} />
              <figcaption className="absolute bottom-0 left-0 right-0 bg-[#f6ead2] px-2 py-1 font-mono text-[0.6rem] uppercase tracking-[0.12em] text-night">
                {fabricNames[s.fabric]}
              </figcaption>
              <span className="absolute left-1/2 top-2 size-2.5 -translate-x-1/2 rounded-full bg-[#f6ead2] shadow" />
            </figure>
          ))}
        </div>
      </div>

      <div className="hero-fade mx-auto mt-10 flex w-full max-w-[90rem] items-center justify-between px-4 font-mono text-[0.68rem] uppercase tracking-[0.14em] text-[#f6ead2]/45 sm:px-8">
        <span>14 jours offerts · sans carte bancaire</span>
        <span className="hidden sm:inline">Défiler ↓</span>
      </div>
    </section>
  );
}
