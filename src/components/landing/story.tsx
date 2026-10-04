"use client";

import { useGSAP } from "@gsap/react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { useRef, useState } from "react";
import { cn } from "@/lib/utils";
import { PhoneFrame, ScreenDeliver, ScreenDictate, ScreenReview, ScreenSew } from "./phone";
import { Label, RevealLines } from "./reveal";

gsap.registerPlugin(ScrollTrigger, useGSAP);

const steps = [
  {
    title: "Dictez.",
    text: "Les mains dans le tissu, vous parlez comme à votre apprenti. Français, wolof, bambara : l'assistant comprend le client, la tenue, la date, le prix et l'acompte.",
    Screen: ScreenDictate,
  },
  {
    title: "Vérifiez.",
    text: "La fiche client et la commande arrivent remplies. Vous corrigez un chiffre si besoin, vous validez d'un geste. Rien n'est enregistré sans vous.",
    Screen: ScreenReview,
  },
  {
    title: "Cousez.",
    text: "Coupe, couture, finitions : chaque tenue avance et l'équipe sait quoi faire. Les retards se voient avant que le client n'appelle.",
    Screen: ScreenSew,
  },
  {
    title: "Livrez, encaissez.",
    text: "Le message WhatsApp est déjà rédigé, le client paie le reste par Wave ou Orange Money, la caisse du soir tombe juste.",
    Screen: ScreenDeliver,
  },
];

export function Story() {
  const ref = useRef<HTMLElement>(null);
  const [active, setActive] = useState(0);

  useGSAP(
    () => {
      gsap.utils.toArray<HTMLElement>(".story-step").forEach((el, i) => {
        ScrollTrigger.create({
          trigger: el,
          start: "top 55%",
          end: "bottom 55%",
          onToggle: (self) => self.isActive && setActive(i),
        });
      });
    },
    { scope: ref },
  );

  return (
    <section id="histoire" ref={ref} className="bg-bg px-4 pb-24 sm:px-8 sm:pb-36">
      <div className="mx-auto max-w-[90rem] border-t border-line pt-10">
        <div className="grid gap-6 lg:grid-cols-[1fr_3fr]">
          <Label n="02" className="text-muted">
            Comment ça marche
          </Label>
          <RevealLines
            className="font-display text-[clamp(2.6rem,7vw,6.5rem)] font-light leading-[0.92] tracking-[-0.035em] text-ink"
            lines={["Une commande,", <em key="e" className="italic text-clay">de la voix à la livraison.</em>]}
          />
        </div>

        <div className="mt-16 grid gap-10 lg:mt-24 lg:grid-cols-2 lg:gap-20">
          {/* étapes */}
          <ol>
            {steps.map((s, i) => (
              <li key={s.title} className="story-step flex min-h-[auto] flex-col justify-center border-t border-line py-12 lg:min-h-[78vh] lg:py-0">
                <div className={cn("transition-opacity duration-500 lg:opacity-25", active === i && "lg:opacity-100")}>
                  <p className="font-mono text-sm text-clay">0{i + 1} / 04</p>
                  <h3 className="mt-4 font-display text-5xl font-light tracking-[-0.03em] text-ink sm:text-6xl">{s.title}</h3>
                  <p className="mt-5 max-w-md text-lg leading-relaxed text-ink-soft">{s.text}</p>
                </div>
                {/* mobile : l'écran sous chaque étape */}
                <div className="mt-10 flex justify-center lg:hidden">
                  <PhoneFrame>
                    <s.Screen />
                  </PhoneFrame>
                </div>
              </li>
            ))}
          </ol>

          {/* ordinateur : téléphone qui reste à l'écran */}
          <div className="relative hidden lg:block">
            <div className="sticky top-[calc(50vh-20rem)] flex justify-center">
              <div className="relative">
                <div aria-hidden className="absolute -inset-x-16 inset-y-10 -z-10 rounded-[3rem] bg-surface-2" />
                <PhoneFrame>
                  {steps.map((s, i) => (
                    <div
                      key={s.title}
                      className={cn(
                        "absolute inset-0 transition-all duration-700 ease-[cubic-bezier(.2,.7,.2,1)]",
                        i === active ? "translate-y-0 opacity-100" : i < active ? "-translate-y-6 opacity-0" : "translate-y-6 opacity-0",
                      )}
                      aria-hidden={i !== active}
                    >
                      <s.Screen />
                    </div>
                  ))}
                </PhoneFrame>
                <div className="absolute -right-14 top-1/2 flex -translate-y-1/2 flex-col gap-2">
                  {steps.map((s, i) => (
                    <span key={s.title} className={cn("h-8 w-1 rounded-full transition-colors", i === active ? "bg-clay" : "bg-line")} />
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
