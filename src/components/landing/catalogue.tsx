"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { useRef } from "react";
import { Label, RevealLines } from "./reveal";
import { Swatch, type Fabric } from "./swatch";

gsap.registerPlugin(ScrollTrigger, useGSAP);

const features: { title: string; text: string; fabric: Fabric; detail: string }[] = [
  { title: "Mesures", text: "Chaque client, ses mesures, leur historique. Retrouvées en deux secondes, même dix ans après.", fabric: "bogolan", detail: "Poitrine 94 · Taille 76 · Bassin 102" },
  { title: "Caisse", text: "Acomptes, restes à payer, Wave, Orange Money, espèces. La clôture du soir tombe juste.", fabric: "kente", detail: "+90 000 F aujourd'hui" },
  { title: "Production", text: "Coupe, couture, finitions, prête. Chaque tenue avance, chacun sait quoi faire.", fabric: "indigo", detail: "5 en couture · 2 en retard" },
  { title: "WhatsApp", text: "Reçu d'acompte, « c'est prêt », rappel du reste : le message est rédigé, vous envoyez.", fabric: "wax", detail: "Message prêt en un geste" },
  { title: "Espace client", text: "Un lien privé par commande : avancement, reste à payer, contact. Pas de compte à créer.", fabric: "bazin", detail: "mansa.app/s/aw7k2p" },
  { title: "Hors ligne", text: "Le réseau coupe ? L'application continue. Tout se synchronise au retour.", fabric: "bogolan", detail: "Dernières données sur le téléphone" },
  { title: "Équipe", text: "Gérant, tailleur, apprenti, brodeuse : chacun voit ce qu'il doit voir.", fabric: "kente", detail: "Rôles et droits" },
  { title: "Tissus", text: "Métrages, fournitures, alertes avant la rupture. Plus de « il n'en reste plus ».", fabric: "wax", detail: "Bazin bleu nuit · 12 m" },
];

export function Catalogue() {
  const ref = useRef<HTMLElement>(null);
  const track = useRef<HTMLDivElement>(null);

  useGSAP(
    () => {
      const mm = gsap.matchMedia();
      // ordinateur : la section se fige et les étoffes défilent à l'horizontale
      mm.add("(min-width: 1024px) and (prefers-reduced-motion: no-preference)", () => {
        const el = track.current!;
        const distance = () => el.scrollWidth - window.innerWidth;
        gsap.to(el, {
          x: () => -distance(),
          ease: "none",
          scrollTrigger: {
            trigger: ref.current,
            start: "top top",
            end: () => `+=${distance()}`,
            pin: true,
            scrub: 0.8,
            invalidateOnRefresh: true,
          },
        });
        gsap.utils.toArray<HTMLElement>(".cat-swatch").forEach((sw) => {
          gsap.fromTo(sw, { scale: 1.25 }, { scale: 1, ease: "none", scrollTrigger: { trigger: ref.current, start: "top top", end: () => `+=${distance()}`, scrub: true } });
        });
      });
    },
    { scope: ref },
  );

  return (
    <section id="atelier" ref={ref} className="overflow-hidden bg-night text-[#f6ead2] lg:h-svh">
      <div className="flex h-full flex-col py-20 lg:pb-10 lg:pt-24">
        <div className="mx-auto grid w-full max-w-[90rem] gap-6 px-4 sm:px-8 lg:grid-cols-[1fr_3fr]">
          <Label n="03" className="text-[#f6ead2]/55">
            Tout l&apos;atelier
          </Label>
          <RevealLines
            className="font-display text-[clamp(2.4rem,5.5vw,5rem)] font-light leading-[0.95] tracking-[-0.03em]"
            lines={["Huit outils, une seule app,", <em key="e" className="italic text-gold">taillés pour l&apos;atelier.</em>]}
          />
        </div>

        <div className="no-scrollbar mt-10 min-h-0 flex-1 snap-x snap-mandatory overflow-x-auto lg:snap-none lg:overflow-visible">
          <div ref={track} className="flex h-full w-max gap-4 px-4 sm:gap-6 sm:px-8 lg:pl-[max(2rem,calc((100vw-90rem)/2+2rem))]">
            {features.map((f, i) => (
              <article key={f.title} className="flex w-[78vw] shrink-0 snap-center flex-col sm:w-[22rem] lg:h-full lg:w-[26rem]">
                <div className="relative h-56 overflow-hidden rounded-[3px] sm:h-64 lg:h-auto lg:min-h-0 lg:flex-1">
                  <div className="cat-swatch h-full w-full origin-center">
                    <Swatch fabric={f.fabric} />
                  </div>
                  <span className="absolute left-4 top-4 rounded-full bg-[#f6ead2] px-3 py-1 font-mono text-[0.65rem] uppercase tracking-[0.12em] text-night">
                    {f.detail}
                  </span>
                </div>
                <div className="flex items-baseline gap-4 border-b border-[#f6ead2]/15 pb-5 pt-6">
                  <span className="font-mono text-xs text-[#f6ead2]/45">{String(i + 1).padStart(2, "0")}</span>
                  <div>
                    <h3 className="font-display text-3xl font-light tracking-tight">{f.title}</h3>
                    <p className="mt-2 max-w-sm text-[0.95rem] leading-relaxed text-[#f6ead2]/65">{f.text}</p>
                  </div>
                </div>
              </article>
            ))}
            <div aria-hidden className="w-4 shrink-0 lg:w-[10vw]" />
          </div>
        </div>
      </div>
    </section>
  );
}
