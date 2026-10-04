"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { Fragment, useRef } from "react";
import { Label } from "./reveal";
import { Swatch, type Fabric } from "./swatch";

gsap.registerPlugin(ScrollTrigger, useGSAP);

/** Texte du manifeste ; « [wax] » insère un petit échantillon de tissu dans la phrase. */
const text =
  "Un tailleur ne devrait pas passer ses soirées à chercher un tour de taille dans un cahier [bogolan] taché, à recompter la caisse ou à répondre « c'est prêt ? » au téléphone. Mansa Vibes garde les mesures, l'argent et les clients [wax] — vous gardez les ciseaux.";

export function Manifesto() {
  const ref = useRef<HTMLElement>(null);

  useGSAP(
    () => {
      const mm = gsap.matchMedia();
      mm.add("(prefers-reduced-motion: no-preference)", () => {
        gsap.fromTo(
          ".mf-word",
          { opacity: 0.14 },
          { opacity: 1, ease: "none", stagger: 0.05, scrollTrigger: { trigger: ".mf-text", start: "top 80%", end: "bottom 45%", scrub: true } },
        );
      });
    },
    { scope: ref },
  );

  const tokens = text.split(" ");

  return (
    <section ref={ref} className="bg-bg px-4 py-28 sm:px-8 sm:py-40">
      <div className="mx-auto grid max-w-[90rem] gap-10 lg:grid-cols-[1fr_3fr]">
        <Label n="01" className="text-muted">
          Pourquoi
        </Label>
        <p className="mf-text font-display text-[clamp(1.9rem,4.4vw,4.2rem)] font-light leading-[1.08] tracking-[-0.02em] text-ink">
          {tokens.map((t, i) => {
            const swatch = t.match(/^\[(\w+)\]$/);
            return (
              <Fragment key={i}>
                {swatch ? (
                  <span className="mf-word mx-[0.1em] inline-block h-[0.78em] w-[1.5em] translate-y-[0.06em] overflow-hidden rounded-full align-baseline">
                    <Swatch fabric={swatch[1] as Fabric} />
                  </span>
                ) : (
                  <span className="mf-word">{t}</span>
                )}{" "}
              </Fragment>
            );
          })}
        </p>
      </div>
    </section>
  );
}
