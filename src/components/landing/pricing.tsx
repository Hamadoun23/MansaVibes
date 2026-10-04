"use client";

import Link from "next/link";
import { useState } from "react";
import { cn, fcfa } from "@/lib/utils";
import { Label, RevealLines } from "./reveal";

const plans = [
  {
    name: "Découverte",
    for: "Pour se lancer seul",
    monthly: 0,
    cta: "Commencer",
    features: ["1 utilisateur", "50 clients et leurs mesures", "Commandes et acomptes", "Lien de suivi client", "Fonctionne hors ligne"],
  },
  {
    name: "Atelier",
    for: "Le choix de la plupart des tailleurs",
    monthly: 7900,
    featured: true,
    cta: "Essayer 14 jours",
    features: ["Jusqu'à 5 personnes", "Clients et commandes illimités", "Assistant vocal", "Messages WhatsApp en un geste", "Caisse Wave, Orange Money, espèces", "Stock de tissus"],
  },
  {
    name: "Maison",
    for: "Plusieurs boutiques, une vue",
    monthly: 19900,
    cta: "Nous écrire",
    features: ["Équipe illimitée, rôles et droits", "Plusieurs ateliers", "Rapports et export comptable", "Import de votre cahier par nos soins", "Accompagnement dédié"],
  },
];

export function Pricing() {
  const [yearly, setYearly] = useState(true);

  return (
    <section id="tarifs" className="bg-surface-2 px-4 py-28 sm:px-8 sm:py-36">
      <div className="mx-auto max-w-[90rem]">
        <div className="grid gap-6 lg:grid-cols-[1fr_3fr]">
          <Label n="05" className="text-muted">
            Tarifs
          </Label>
          <div className="flex flex-wrap items-end justify-between gap-8">
            <RevealLines
              className="font-display text-[clamp(2.6rem,6vw,5.5rem)] font-light leading-[0.92] tracking-[-0.035em] text-ink"
              lines={["Moins cher qu'un", <em key="e" className="italic text-clay">mètre de bazin.</em>]}
            />
            <div role="radiogroup" aria-label="Période de facturation" className="flex border border-ink font-mono text-[0.72rem] uppercase tracking-[0.12em]">
              {[
                { v: false, l: "Mensuel" },
                { v: true, l: "Annuel · 2 mois offerts" },
              ].map((o) => (
                <button
                  key={o.l}
                  type="button"
                  role="radio"
                  aria-checked={yearly === o.v}
                  onClick={() => setYearly(o.v)}
                  className={cn("px-4 py-3 transition", yearly === o.v ? "bg-ink text-bg" : "text-ink hover:bg-ink/5")}
                >
                  {o.l}
                </button>
              ))}
            </div>
          </div>
        </div>

        <div className="mt-16 grid border-t border-ink lg:grid-cols-3">
          {plans.map((p) => {
            const price = yearly ? Math.round((p.monthly * 10) / 12 / 100) * 100 : p.monthly;
            return (
              <div
                key={p.name}
                className={cn(
                  "flex flex-col border-b border-ink/15 px-0 py-10 lg:border-b-0 lg:border-r lg:px-8 lg:last:border-r-0",
                  p.featured && "-mx-4 bg-night px-4 text-[#f6ead2] sm:-mx-8 sm:px-8 lg:mx-0",
                )}
              >
                <div className="flex items-baseline justify-between">
                  <h3 className="font-display text-3xl font-light">{p.name}</h3>
                  {p.featured && <span className="font-mono text-[0.65rem] uppercase tracking-[0.12em] text-gold">Le plus choisi</span>}
                </div>
                <p className={cn("mt-1 text-sm", p.featured ? "text-[#f6ead2]/60" : "text-muted")}>{p.for}</p>
                <p className="mt-10 font-display text-6xl font-light tracking-tight">
                  {p.monthly === 0 ? "0 F" : fcfa(price)}
                  <span className={cn("ml-2 font-sans text-sm", p.featured ? "text-[#f6ead2]/60" : "text-muted")}>/ mois</span>
                </p>
                <p className={cn("mt-2 h-5 font-mono text-[0.7rem] uppercase tracking-[0.1em]", p.featured ? "text-gold" : "text-muted")}>
                  {p.monthly > 0 && yearly ? `${fcfa(p.monthly * 10)} par an` : p.monthly > 0 ? "sans engagement" : "pour toujours"}
                </p>
                <ul className={cn("mt-8 flex-1 border-t", p.featured ? "border-[#f6ead2]/15" : "border-ink/10")}>
                  {p.features.map((f) => (
                    <li key={f} className={cn("border-b py-3 text-[0.95rem]", p.featured ? "border-[#f6ead2]/10 text-[#f6ead2]/85" : "border-ink/10 text-ink-soft")}>
                      {f}
                    </li>
                  ))}
                </ul>
                <Link
                  href="/inscription"
                  className={cn(
                    "mt-10 flex h-14 items-center justify-between rounded-full px-6 font-semibold transition",
                    p.featured ? "bg-gold text-gold-ink hover:bg-[#f6ead2]" : "border border-ink text-ink hover:bg-ink hover:text-bg",
                  )}
                >
                  {p.cta} <span aria-hidden>→</span>
                </Link>
              </div>
            );
          })}
        </div>
        <p className="mt-8 font-mono text-[0.7rem] uppercase tracking-[0.1em] text-muted">Paiement Wave, Orange Money ou carte · prix en francs CFA, taxes comprises</p>
      </div>
    </section>
  );
}
