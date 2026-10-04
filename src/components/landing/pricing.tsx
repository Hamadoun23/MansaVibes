"use client";

import { Check } from "lucide-react";
import { useState } from "react";
import { ButtonLink } from "@/components/ui";
import { cn, fcfa } from "@/lib/utils";

const plans = [
  {
    name: "Découverte",
    tagline: "Pour se lancer seul·e",
    monthly: 0,
    cta: "Commencer gratuitement",
    features: ["1 utilisateur", "50 clients et leurs mesures", "Commandes et acomptes", "Lien de suivi client", "Fonctionne hors ligne"],
  },
  {
    name: "Atelier",
    tagline: "Le choix de la plupart des tailleurs",
    monthly: 7900,
    featured: true,
    cta: "Essayer 14 jours",
    features: [
      "Jusqu'à 5 membres d'équipe",
      "Clients et commandes illimités",
      "Assistant vocal · 300 notes / mois",
      "Rappels WhatsApp en un tap",
      "Caisse : Wave, Orange Money, espèces",
      "Stock de tissus et fournitures",
    ],
  },
  {
    name: "Maison",
    tagline: "Plusieurs boutiques, une vue",
    monthly: 19900,
    cta: "Parler à un conseiller",
    features: [
      "Équipe illimitée, rôles et droits",
      "Plusieurs ateliers / boutiques",
      "Assistant vocal illimité",
      "Rapports et export comptable",
      "Boutique en ligne (bientôt)",
      "Accompagnement dédié",
    ],
  },
];

export function Pricing() {
  const [yearly, setYearly] = useState(true);

  return (
    <div>
      <div className="flex justify-center">
        <div role="radiogroup" aria-label="Période de facturation" className="inline-flex rounded-full border border-line bg-surface p-1 text-sm font-bold">
          {[
            { v: false, l: "Mensuel" },
            { v: true, l: "Annuel" },
          ].map((o) => (
            <button
              key={o.l}
              type="button"
              role="radio"
              aria-checked={yearly === o.v}
              onClick={() => setYearly(o.v)}
              className={cn("flex items-center gap-2 rounded-full px-5 py-2 transition", yearly === o.v ? "bg-ink text-bg" : "text-muted hover:text-ink")}
            >
              {o.l}
              {o.v && <span className={cn("rounded-full px-1.5 py-0.5 text-[0.65rem]", yearly ? "bg-gold text-gold-ink" : "bg-gold-soft text-clay")}>2 mois offerts</span>}
            </button>
          ))}
        </div>
      </div>

      <div className="mt-10 grid gap-4 lg:grid-cols-3 lg:items-stretch">
        {plans.map((p) => {
          const price = yearly ? Math.round((p.monthly * 10) / 12 / 100) * 100 : p.monthly;
          return (
            <div
              key={p.name}
              className={cn(
                "relative flex flex-col rounded-[2rem] p-6 sm:p-8",
                p.featured ? "bg-night text-white shadow-[0_30px_80px_-30px_var(--gold)] grain lg:-my-4 lg:py-12" : "border border-line bg-surface",
              )}
            >
              {p.featured && (
                <span className="absolute -top-3 left-6 rounded-full bg-gold px-3 py-1 text-xs font-bold text-gold-ink">Le plus choisi</span>
              )}
              <p className="font-display text-2xl font-semibold">{p.name}</p>
              <p className={cn("mt-1 text-sm", p.featured ? "text-white/60" : "text-muted")}>{p.tagline}</p>
              <p className="mt-6 flex items-baseline gap-1.5">
                <span className="font-display text-5xl font-semibold tracking-tight">{p.monthly === 0 ? "0 F" : fcfa(price)}</span>
                <span className={cn("text-sm", p.featured ? "text-white/60" : "text-muted")}>/ mois</span>
              </p>
              <p className={cn("mt-1 h-5 text-xs", p.featured ? "text-gold" : "text-muted")}>
                {p.monthly > 0 && yearly ? `soit ${fcfa(p.monthly * 10)} par an` : p.monthly > 0 ? "sans engagement" : "pour toujours"}
              </p>
              <ul className="mt-7 flex-1 space-y-3">
                {p.features.map((f) => (
                  <li key={f} className="flex gap-3 text-sm">
                    <Check className={cn("mt-0.5 size-4 shrink-0", p.featured ? "text-gold" : "text-leaf")} />
                    <span className={p.featured ? "text-white/85" : "text-ink-soft"}>{f}</span>
                  </li>
                ))}
              </ul>
              <ButtonLink href="/inscription" variant={p.featured ? "gold" : "outline"} className="mt-8 w-full">
                {p.cta}
              </ButtonLink>
            </div>
          );
        })}
      </div>
      <p className="mt-8 text-center text-sm text-muted">Paiement par Wave, Orange Money ou carte. Prix en francs CFA (XOF), taxes comprises.</p>
    </div>
  );
}
