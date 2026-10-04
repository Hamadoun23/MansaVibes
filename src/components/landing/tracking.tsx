"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import Link from "next/link";
import { useRef } from "react";
import { cn } from "@/lib/utils";
import { PhoneFrame } from "./phone";
import { Label, RevealLines } from "./reveal";
import { Swatch } from "./swatch";

gsap.registerPlugin(ScrollTrigger, useGSAP);

function TrackingScreen() {
  return (
    <div className="flex h-full flex-col">
      <div className="relative h-40 overflow-hidden">
        <Swatch fabric="bazin" />
        <div className="absolute inset-0 bg-gradient-to-t from-[#14112a] to-transparent" />
        <p className="absolute bottom-3 left-5 font-display text-2xl leading-tight text-white">
          Votre boubou
          <br />
          <em className="text-[#f0b54b]">avance.</em>
        </p>
      </div>
      <ol className="mx-5 mt-4">
        {["Commande reçue", "Coupe", "Couture", "Finitions", "Prête"].map((s, i) => (
          <li key={s} className="flex items-center gap-3 border-b border-[#e6dccb] py-2.5 text-sm">
            <span className={cn("size-2.5 rounded-full", i < 3 ? "bg-[#1d8556]" : i === 3 ? "bg-[#e3a33a] ring-4 ring-[#e3a33a]/25" : "bg-[#ece3d3]")} />
            <span className={i <= 3 ? "font-bold" : "text-[#6e6880]"}>{s}</span>
            {i === 3 && <span className="ml-auto font-mono text-[0.6rem] uppercase text-[#c4512f]">en cours</span>}
          </li>
        ))}
      </ol>
      <div className="mx-5 mb-6 mt-auto flex items-center justify-between rounded-2xl bg-[#14112a] p-4 text-white">
        <div>
          <p className="text-[0.65rem] text-white/60">Reste à payer</p>
          <p className="font-display text-xl">25 000 F</p>
        </div>
        <span className="rounded-full bg-[#e3a33a] px-3 py-2 text-xs font-bold text-[#2b1b02]">Payer par Wave</span>
      </div>
    </div>
  );
}

export function Tracking() {
  const ref = useRef<HTMLElement>(null);
  useGSAP(
    () => {
      const mm = gsap.matchMedia();
      mm.add("(prefers-reduced-motion: no-preference)", () => {
        gsap.fromTo(".trk-phone", { y: 80, rotate: 4 }, { y: -60, rotate: -2, ease: "none", scrollTrigger: { trigger: ref.current, start: "top bottom", end: "bottom top", scrub: true } });
        gsap.fromTo(".trk-swatch", { yPercent: -15 }, { yPercent: 15, ease: "none", scrollTrigger: { trigger: ref.current, start: "top bottom", end: "bottom top", scrub: true } });
      });
    },
    { scope: ref },
  );

  return (
    <section ref={ref} className="overflow-hidden bg-bg px-4 py-28 sm:px-8 sm:py-40">
      <div className="mx-auto grid max-w-[90rem] items-center gap-16 lg:grid-cols-2">
        <div>
          <Label n="04" className="text-muted">
            Espace client
          </Label>
          <RevealLines
            className="mt-8 font-display text-[clamp(2.8rem,7vw,6.5rem)] font-light leading-[0.9] tracking-[-0.035em] text-ink"
            lines={["Le client suit.", <em key="e" className="italic text-clay">Vous cousez.</em>]}
          />
          <p className="mt-8 max-w-md text-lg leading-relaxed text-ink-soft">
            Chaque commande a son lien privé, envoyé sur WhatsApp : avancement, reste à payer, mesures, contact de l&apos;atelier. Pas de compte, pas
            d&apos;application à installer — et plus d&apos;appels « c&apos;est prêt ? ».
          </p>
          <dl className="mt-10 grid max-w-md grid-cols-3 border-t border-line font-mono text-[0.7rem] uppercase tracking-[0.1em]">
            {[
              ["Lien", "privé"],
              ["Compte", "aucun"],
              ["Paiement", "Wave · OM"],
            ].map(([k, v]) => (
              <div key={k} className="border-r border-line py-4 pr-3 last:border-r-0 [&:not(:first-child)]:pl-4">
                <dt className="text-muted">{k}</dt>
                <dd className="mt-1 text-ink">{v}</dd>
              </div>
            ))}
          </dl>
          <Link href="/suivi/aw7k2p" className="mt-10 inline-flex items-center gap-3 border-b border-ink pb-1 font-semibold text-ink transition hover:gap-5">
            Voir un exemple de suivi <span aria-hidden>→</span>
          </Link>
        </div>

        <div className="relative flex justify-center py-10">
          <div className="absolute inset-y-0 left-[12%] right-[12%] overflow-hidden rounded-[3px]" aria-hidden>
            <div className="trk-swatch h-[130%] w-full">
              <Swatch fabric="wax" />
            </div>
          </div>
          <PhoneFrame className="trk-phone relative">
            <TrackingScreen />
          </PhoneFrame>
        </div>
      </div>
    </section>
  );
}
