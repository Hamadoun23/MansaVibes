import { ArrowRight, Check, MessageCircle, Mic, Play, Star } from "lucide-react";
import { Bogolan } from "@/components/brand";
import { ButtonLink } from "@/components/ui";
import { PhoneFrame, PhoneTodayScreen } from "./phone";

export function Hero() {
  return (
    <section className="relative overflow-hidden pb-16 pt-28 sm:pt-36 lg:pb-28">
      {/* fond : halo + motif */}
      <div aria-hidden className="absolute inset-0 -z-10">
        <div className="absolute -right-40 -top-40 size-[42rem] rounded-full bg-gold/25 blur-[120px]" />
        <div className="absolute -left-40 top-60 size-[30rem] rounded-full bg-clay/15 blur-[120px]" />
        <Bogolan className="text-ink/[0.045] [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" id="hero-bogolan" />
      </div>

      <div className="container-page grid items-center gap-14 lg:grid-cols-[1.1fr_0.9fr] lg:gap-8">
        <div className="animate-rise text-center lg:text-left">
          <a
            href="#assistant"
            className="inline-flex items-center gap-2 rounded-full border border-line bg-surface/70 py-1.5 pl-1.5 pr-4 text-xs font-semibold text-ink-soft backdrop-blur transition hover:border-gold"
          >
            <span className="rounded-full bg-night px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-gold">Nouveau</span>
            Dictez une commande, on remplit tout
            <ArrowRight className="size-3.5" />
          </a>

          <h1 className="mt-7 font-display text-[2.85rem] font-medium leading-[0.98] tracking-[-0.03em] text-ink sm:text-6xl lg:text-[5.2rem]">
            Votre atelier,
            <br />
            cousu{" "}
            <span className="relative inline-block italic text-clay">
              main
              <svg viewBox="0 0 200 20" className="absolute -bottom-2 left-0 w-full text-gold" aria-hidden preserveAspectRatio="none">
                <path d="M2 12 Q 50 2, 100 10 T 198 8" fill="none" stroke="currentColor" strokeWidth="3" strokeDasharray="7 6" strokeLinecap="round" />
              </svg>
            </span>
            <br />
            géré au doigt.
          </h1>

          <p className="mx-auto mt-7 max-w-xl text-lg leading-relaxed text-ink-soft lg:mx-0">
            Mesures, commandes, acomptes Wave & Orange Money, rappels WhatsApp. Mansa Vibes remplace le cahier, la calculette et les
            vocaux perdus — <strong className="text-ink">depuis votre téléphone, même avec une connexion faible.</strong>
          </p>

          <div className="mt-9 flex flex-col items-center gap-3 sm:flex-row sm:justify-center lg:justify-start">
            <ButtonLink href="/inscription" size="lg" className="w-full sm:w-auto">
              Ouvrir mon atelier — gratuit
              <ArrowRight className="size-4" />
            </ButtonLink>
            <ButtonLink href="/app" size="lg" variant="outline" className="w-full sm:w-auto">
              <Play className="size-4 fill-current" />
              Voir la démo
            </ButtonLink>
          </div>

          <ul className="mt-7 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm font-medium text-muted lg:justify-start">
            {["14 jours offerts", "Sans carte bancaire", "Prêt en 2 minutes"].map((t) => (
              <li key={t} className="flex items-center gap-1.5">
                <Check className="size-4 text-leaf" />
                {t}
              </li>
            ))}
          </ul>

          <div className="mt-10 flex items-center justify-center gap-4 lg:justify-start">
            <div className="flex -space-x-2.5">
              {["#e3a33a", "#c4512f", "#1d8556", "#3a5bd9"].map((c, i) => (
                <span key={c} className="grid size-9 place-items-center rounded-full text-xs font-bold text-white ring-2 ring-bg" style={{ background: c }}>
                  {["AN", "MD", "FS", "KB"][i]}
                </span>
              ))}
            </div>
            <div className="text-left">
              <div className="flex gap-0.5 text-gold">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star key={i} className="size-3.5 fill-current" />
                ))}
              </div>
              <p className="text-xs font-semibold text-muted">
                <span className="text-ink">1 200+ ateliers</span> en Afrique de l&apos;Ouest
              </p>
            </div>
          </div>
        </div>

        {/* visuel */}
        <div className="relative mx-auto flex justify-center">
          <div aria-hidden className="absolute inset-x-6 bottom-6 top-16 -z-10 rounded-[3rem] bg-night grain">
            <Bogolan className="text-gold/10" id="hero-card-bogolan" />
          </div>
          <PhoneFrame className="animate-float-slow rotate-[-2deg]">
            <PhoneTodayScreen />
          </PhoneFrame>

          {/* cartes flottantes */}
          <div className="absolute -left-4 top-24 w-52 animate-float rounded-2xl border border-line bg-surface/95 p-3 shadow-xl backdrop-blur sm:-left-16 sm:w-60">
            <div className="flex items-center gap-2">
              <span className="relative grid size-8 place-items-center rounded-full bg-gold text-gold-ink">
                <span className="absolute inset-0 animate-pulse-ring rounded-full bg-gold" />
                <Mic className="relative size-4" />
              </span>
              <div className="flex h-6 flex-1 items-center gap-[3px]">
                {Array.from({ length: 18 }).map((_, i) => (
                  <span key={i} className="wave-bar h-full w-[3px] rounded-full bg-ink/60" style={{ animationDelay: `${(i % 6) * 0.12}s` }} />
                ))}
              </div>
            </div>
            <p className="mt-2 text-[0.7rem] leading-snug text-ink-soft">« Boubou bazin pour Awa, samedi, 45 000, acompte 20 000 en Wave »</p>
          </div>

          <div className="absolute -right-2 bottom-28 w-56 animate-float rounded-2xl border border-line bg-surface/95 p-3 shadow-xl backdrop-blur [animation-delay:1.5s] sm:-right-14">
            <div className="flex items-start gap-2.5">
              <span className="grid size-8 shrink-0 place-items-center rounded-full bg-[#25d366] text-white">
                <MessageCircle className="size-4" />
              </span>
              <div>
                <p className="text-[0.7rem] font-bold text-ink">WhatsApp envoyé</p>
                <p className="text-[0.7rem] leading-snug text-muted">Bonjour Khady, votre ensemble est prêt ✨ Reste : 15 000 F</p>
              </div>
            </div>
          </div>

          <div className="absolute -right-1 top-10 rounded-2xl bg-leaf px-3.5 py-2.5 text-white shadow-xl animate-float [animation-delay:3s] sm:-right-6">
            <p className="text-[0.6rem] font-bold uppercase tracking-wider opacity-80">Wave reçu</p>
            <p className="font-display text-lg font-semibold">+20 000 F</p>
          </div>
        </div>
      </div>
    </section>
  );
}
