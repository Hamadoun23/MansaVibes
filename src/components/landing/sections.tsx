import {
  ArrowRight,
  BookX,
  Boxes,
  Calculator,
  CheckCircle2,
  Clock3,
  KanbanSquare,
  Link2,
  MessageCircle,
  Mic,
  Ruler,
  ShieldCheck,
  Smartphone,
  Users,
  Wallet,
  WifiOff,
} from "lucide-react";
import Link from "next/link";
import { Bogolan, Logo } from "@/components/brand";
import { Avatar, ButtonLink, SectionEyebrow } from "@/components/ui";
import { cn } from "@/lib/utils";
import { PhoneFrame } from "./phone";
import { Pricing } from "./pricing";
import { VoiceDemo } from "./voice-demo";

function SectionTitle({ eyebrow, title, intro, center, light }: { eyebrow: string; title: React.ReactNode; intro?: string; center?: boolean; light?: boolean }) {
  return (
    <div className={cn("max-w-2xl", center && "mx-auto text-center")}>
      <SectionEyebrow className={light ? "text-gold" : undefined}>{eyebrow}</SectionEyebrow>
      <h2 className={cn("mt-4 font-display text-4xl font-medium leading-[1.05] tracking-[-0.02em] sm:text-5xl", light ? "text-white" : "text-ink")}>{title}</h2>
      {intro && <p className={cn("mt-5 text-lg leading-relaxed", light ? "text-white/65" : "text-ink-soft")}>{intro}</p>}
    </div>
  );
}

/* ───────── Bandeau des villes ───────── */
export function CityMarquee() {
  const cities = ["Dakar", "Bamako", "Abidjan", "Ouagadougou", "Conakry", "Lomé", "Cotonou", "Niamey", "Thiès", "Saint-Louis", "Bouaké", "Ségou"];
  return (
    <section aria-label="Présent dans toute l'Afrique de l'Ouest" className="border-y border-line bg-surface/60 py-5">
      <div className="relative overflow-hidden [mask-image:linear-gradient(90deg,transparent,black_12%,black_88%,transparent)]">
        <div className="flex w-max animate-marquee gap-12 pr-12">
          {[...cities, ...cities].map((c, i) => (
            <span key={i} className="flex items-center gap-12 font-display text-2xl italic text-ink/40">
              {c}
              <span className="size-1.5 rounded-full bg-gold" />
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ───────── Avant / après ───────── */
export function Problems() {
  const items = [
    { icon: BookX, before: "Le cahier de mesures taché, introuvable le jour J.", after: "Les mesures de chaque client, retrouvées en 2 secondes." },
    { icon: Calculator, before: "« Elle m'a donné combien déjà ? » Les acomptes au feeling.", after: "Chaque franc tracé : acompte, reste, moyen de paiement." },
    { icon: Clock3, before: "Le client qui appelle 5 fois : « c'est prêt ? »", after: "Un lien de suivi et un WhatsApp automatique quand c'est prêt." },
  ];
  return (
    <section className="py-20 sm:py-28">
      <div className="container-page">
        <SectionTitle
          eyebrow="Pourquoi Mansa Vibes"
          title={
            <>
              Moins de cahiers, <span className="italic text-clay">plus de couture.</span>
            </>
          }
          intro="Un atelier perd en moyenne 4 heures par semaine à chercher des mesures, recompter la caisse et répondre au téléphone. On vous les rend."
        />
        <div className="mt-12 grid gap-4 md:grid-cols-3">
          {items.map((it) => (
            <article key={it.before} className="group rounded-[2rem] border border-line bg-surface p-6 transition hover:-translate-y-1 hover:shadow-xl">
              <span className="grid size-12 place-items-center rounded-2xl bg-clay-soft text-clay">
                <it.icon className="size-5" />
              </span>
              <p className="mt-6 text-ink-soft line-through decoration-clay/50 decoration-2">{it.before}</p>
              <p className="mt-4 flex gap-2.5 text-lg font-semibold leading-snug text-ink">
                <CheckCircle2 className="mt-1 size-5 shrink-0 text-leaf" />
                {it.after}
              </p>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ───────── Assistant vocal ───────── */
export function Assistant() {
  return (
    <section id="assistant" className="scroll-mt-24 px-3 sm:px-4">
      <div className="relative mx-auto max-w-[90rem] overflow-hidden rounded-[2.5rem] bg-night py-20 grain sm:rounded-[3rem] sm:py-28">
        <Bogolan className="text-gold/[0.07] [mask-image:linear-gradient(to_bottom,black,transparent)]" id="assistant-bogolan" />
        <div aria-hidden className="absolute -right-32 top-0 size-[30rem] rounded-full bg-gold/20 blur-[120px]" />
        <div className="container-page relative">
          <div className="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center lg:gap-16">
            <div>
              <SectionTitle
                light
                eyebrow="Assistant vocal"
                title={
                  <>
                    Parlez.
                    <br />
                    <span className="italic text-gold">On s&apos;occupe du reste.</span>
                  </>
                }
                intro="Les mains prises par le tissu ? Dictez la commande comme vous la diriez à votre apprenti. L'assistant crée la fiche client, la commande, l'acompte et prépare le message WhatsApp."
              />
              <ul className="mt-8 space-y-3 text-white/80">
                {["Comprend le français mêlé de wolof, bambara, dioula", "Rien n'est enregistré sans votre validation", "Vos notes vocales restent privées"].map((t) => (
                  <li key={t} className="flex items-center gap-3">
                    <span className="grid size-6 place-items-center rounded-full bg-gold/15 text-gold">
                      <CheckCircle2 className="size-3.5" />
                    </span>
                    {t}
                  </li>
                ))}
              </ul>
            </div>
            <VoiceDemo />
          </div>
        </div>
      </div>
    </section>
  );
}

/* ───────── Fonctionnalités (bento) ───────── */
export function Features() {
  return (
    <section id="fonctionnalites" className="scroll-mt-24 py-20 sm:py-28">
      <div className="container-page">
        <SectionTitle
          center
          eyebrow="Tout l'atelier"
          title={
            <>
              Une seule app,
              <br />
              <span className="italic text-clay">du métrage à la livraison.</span>
            </>
          }
        />

        <div className="mt-14 grid auto-rows-[minmax(14rem,auto)] gap-4 md:grid-cols-6">
          {/* mesures */}
          <article className="relative overflow-hidden rounded-[2rem] border border-line bg-surface p-6 md:col-span-4 sm:p-8">
            <FeatureHead icon={Ruler} title="Mesures de chaque client" text="Fiches homme, femme, enfant, modèles personnalisables. L'historique des mesures, pour voir quand ça change." />
            <div className="mt-6 grid grid-cols-2 gap-2 sm:grid-cols-4">
              {[
                ["Poitrine", "94"],
                ["Taille", "76"],
                ["Bassin", "102"],
                ["Épaule", "39"],
                ["Manche", "58"],
                ["Tour de bras", "30"],
                ["Cou", "36"],
                ["Longueur", "142"],
              ].map(([l, v]) => (
                <div key={l} className="rounded-2xl bg-surface-2 px-3 py-2.5">
                  <p className="text-[0.7rem] font-semibold text-muted">{l}</p>
                  <p className="font-display text-xl font-semibold text-ink">
                    {v}
                    <span className="ml-0.5 text-xs font-sans text-muted">cm</span>
                  </p>
                </div>
              ))}
            </div>
          </article>

          {/* caisse */}
          <article className="relative overflow-hidden rounded-[2rem] bg-gold p-6 text-gold-ink md:col-span-2 sm:p-8">
            <Wallet className="size-6" />
            <h3 className="mt-5 font-display text-2xl font-semibold">Caisse claire</h3>
            <p className="mt-2 text-sm font-medium opacity-80">Wave, Orange Money, espèces. Acomptes, restes à payer, clôture du jour.</p>
            <div className="mt-6 space-y-2">
              {[
                ["Wave", "+50 000"],
                ["Orange Money", "+10 000"],
                ["Espèces", "+30 000"],
              ].map(([l, v]) => (
                <div key={l} className="flex justify-between rounded-xl bg-white/35 px-3 py-2 text-sm font-bold">
                  <span>{l}</span>
                  <span>{v} F</span>
                </div>
              ))}
            </div>
          </article>

          {/* production */}
          <article className="rounded-[2rem] border border-line bg-surface p-6 md:col-span-3 sm:p-8">
            <FeatureHead icon={KanbanSquare} title="Suivi de production" text="Coupe, couture, finitions, prête : chaque tenue avance d'un glissement. Les retards sautent aux yeux." />
            <div className="mt-6 flex gap-2 overflow-hidden">
              {[
                { s: "Coupe", n: 3, c: "bg-sky" },
                { s: "Couture", n: 5, c: "bg-sky" },
                { s: "Finitions", n: 2, c: "bg-gold" },
                { s: "Prête", n: 4, c: "bg-leaf" },
              ].map((col) => (
                <div key={col.s} className="flex-1 rounded-2xl bg-surface-2 p-2">
                  <p className="flex items-center gap-1.5 px-1 text-[0.7rem] font-bold text-ink-soft">
                    <span className={cn("size-1.5 rounded-full", col.c)} />
                    {col.s}
                  </p>
                  {Array.from({ length: Math.min(col.n, 3) }).map((_, i) => (
                    <div key={i} className="mt-1.5 h-7 rounded-lg bg-surface ring-1 ring-line" />
                  ))}
                </div>
              ))}
            </div>
          </article>

          {/* whatsapp */}
          <article className="rounded-[2rem] border border-line bg-surface p-6 md:col-span-3 sm:p-8">
            <FeatureHead icon={MessageCircle} title="WhatsApp en un tap" text="Reçu d'acompte, « votre tenue est prête », rappel du reste à payer : le message est rédigé, vous n'avez qu'à envoyer." />
            <div className="mt-6 space-y-2">
              <div className="ml-auto w-fit max-w-[85%] rounded-2xl rounded-br-md bg-[#d9fdd3] px-3.5 py-2 text-sm text-[#111b21]">
                Bonjour Aminata 👋 Votre boubou brodé est prêt ! Reste à régler : 25 000 F. Suivez-le ici : mansa.app/s/aw7k2p
                <span className="mt-0.5 block text-right text-[0.65rem] text-[#667781]">10:42 ✓✓</span>
              </div>
            </div>
          </article>

          {/* petites cartes */}
          {[
            { icon: WifiOff, t: "Hors ligne", d: "Coupure de réseau ? L'app continue. Tout se synchronise au retour." },
            { icon: Users, t: "Équipe & rôles", d: "Gérant, tailleur, apprenti : chacun voit ce qu'il doit voir." },
            { icon: Boxes, t: "Stock de tissus", d: "Métrages, fournitures, alertes de rupture avant la commande." },
          ].map((f) => (
            <article key={f.t} className="rounded-[2rem] border border-line bg-surface p-6 md:col-span-2 sm:p-8">
              <FeatureHead icon={f.icon} title={f.t} text={f.d} />
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}

function FeatureHead({ icon: Icon, title, text }: { icon: React.ComponentType<{ className?: string }>; title: string; text: string }) {
  return (
    <>
      <span className="grid size-11 place-items-center rounded-2xl bg-surface-2 text-ink">
        <Icon className="size-5" />
      </span>
      <h3 className="mt-5 font-display text-2xl font-semibold text-ink">{title}</h3>
      <p className="mt-2 max-w-md text-[0.95rem] leading-relaxed text-ink-soft">{text}</p>
    </>
  );
}

/* ───────── Espace client ───────── */
export function ClientSpace() {
  return (
    <section className="py-20 sm:py-28">
      <div className="container-page grid items-center gap-14 lg:grid-cols-2">
        <div className="order-2 flex justify-center lg:order-1">
          <div className="relative">
            <div aria-hidden className="absolute -inset-10 -z-10 rounded-full bg-leaf/15 blur-3xl" />
            <PhoneFrame className="rotate-[2deg]">
              <div className="flex h-[34rem] flex-col bg-bg px-5 pt-12 text-ink">
                <p className="text-[0.65rem] font-bold uppercase tracking-widest text-muted">Atelier Awa Couture</p>
                <p className="mt-1 font-display text-2xl font-semibold leading-tight">Bonjour Aminata,<br />votre boubou avance ✨</p>
                <div className="mt-5 rounded-2xl bg-surface p-4 ring-1 ring-line">
                  {["Commande reçue", "Coupe", "Couture", "Finitions", "Prête"].map((s, i) => (
                    <div key={s} className="flex items-center gap-3 py-1.5">
                      <span className={cn("grid size-6 place-items-center rounded-full text-[0.65rem] font-bold", i < 3 ? "bg-leaf text-white" : i === 3 ? "bg-gold text-gold-ink" : "bg-surface-3 text-muted")}>
                        {i < 3 ? "✓" : i + 1}
                      </span>
                      <span className={cn("text-sm", i <= 3 ? "font-bold" : "text-muted")}>{s}</span>
                      {i === 3 && <span className="ml-auto text-[0.65rem] font-bold text-clay">en cours</span>}
                    </div>
                  ))}
                </div>
                <div className="mt-3 flex items-center justify-between rounded-2xl bg-night p-4 text-white">
                  <div>
                    <p className="text-[0.65rem] text-white/60">Reste à payer</p>
                    <p className="font-display text-xl font-semibold">25 000 F</p>
                  </div>
                  <span className="rounded-full bg-gold px-3 py-1.5 text-xs font-bold text-gold-ink">Payer par Wave</span>
                </div>
                <p className="mt-3 text-center text-[0.7rem] text-muted">Livraison prévue samedi · 77 412 08 33</p>
              </div>
            </PhoneFrame>
          </div>
        </div>
        <div className="order-1 lg:order-2">
          <SectionTitle
            eyebrow="Espace client"
            title={
              <>
                Vos clients suivent leur tenue.
                <span className="italic text-leaf"> Sans vous appeler.</span>
              </>
            }
            intro="Chaque commande a son lien privé, envoyé par WhatsApp. Avancement, reste à payer, mesures enregistrées, contact de l'atelier — pas de compte, pas d'application à installer."
          />
          <div className="mt-8 grid gap-3 sm:grid-cols-2">
            {[
              { icon: Link2, t: "Lien privé et révocable" },
              { icon: Smartphone, t: "Ouvre sur n'importe quel téléphone" },
              { icon: Wallet, t: "Paiement du reste en ligne" },
              { icon: ShieldCheck, t: "Données protégées" },
            ].map((f) => (
              <div key={f.t} className="flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3.5 text-sm font-semibold text-ink">
                <f.icon className="size-4 text-leaf" />
                {f.t}
              </div>
            ))}
          </div>
          <ButtonLink href="/suivi/aw7k2p" variant="ink" className="mt-8">
            Voir un exemple de suivi <ArrowRight className="size-4" />
          </ButtonLink>
        </div>
      </div>
    </section>
  );
}

/* ───────── Étapes ───────── */
export function Steps() {
  const steps = [
    { n: "01", t: "Créez votre atelier", d: "Nom, logo, numéro WhatsApp. Deux minutes, depuis votre téléphone." },
    { n: "02", t: "Ajoutez vos clients", d: "À la voix, à la main ou en important vos contacts. Les mesures suivent." },
    { n: "03", t: "Encaissez sereinement", d: "Commandes, acomptes, rappels. Vous cousez, Mansa Vibes s'occupe du reste." },
  ];
  return (
    <section className="border-y border-line bg-surface-2/60 py-20 sm:py-24">
      <div className="container-page">
        <SectionTitle center eyebrow="Démarrer" title={<>Prêt avant votre prochain client.</>} />
        <ol className="mt-14 grid gap-8 md:grid-cols-3 md:gap-6">
          {steps.map((s, i) => (
            <li key={s.n} className="relative">
              {i < steps.length - 1 && <span aria-hidden className="absolute left-16 right-0 top-7 hidden border-t-2 border-dashed border-gold/50 md:block" />}
              <span className="relative grid size-14 place-items-center rounded-2xl bg-night font-display text-xl font-semibold text-gold">{s.n}</span>
              <h3 className="mt-5 font-display text-2xl font-semibold text-ink">{s.t}</h3>
              <p className="mt-2 text-ink-soft">{s.d}</p>
            </li>
          ))}
        </ol>
      </div>
    </section>
  );
}

/* ───────── Témoignages ───────── */
export function Testimonials() {
  const quotes = [
    { q: "Avant je perdais des clientes parce que je ne retrouvais plus leurs mesures. Maintenant elles reviennent, et elles paient le reste par Wave.", n: "Awa Ndiaye", r: "Awa Couture · Dakar" },
    { q: "Je dicte la commande pendant que je coupe. Le soir, la caisse est juste. Mon frère qui gère la boutique de Kalaban voit tout.", n: "Moussa Traoré", r: "Maison Traoré · Bamako" },
    { q: "Les clientes ne m'appellent plus pour savoir si c'est prêt : elles ont le lien. J'ai gagné mes soirées.", n: "Adjoua Kouassi", r: "Adjoua Création · Abidjan" },
  ];
  return (
    <section className="py-20 sm:py-28">
      <div className="container-page">
        <SectionTitle eyebrow="Ils cousent avec nous" title={<>Des ateliers <span className="italic text-clay">qui respirent.</span></>} />
        <div className="no-scrollbar -mx-4 mt-12 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0">
          {quotes.map((t, i) => (
            <figure
              key={t.n}
              className={cn("flex w-[85%] shrink-0 snap-center flex-col rounded-[2rem] p-6 sm:p-8 md:w-auto", i === 1 ? "bg-night text-white" : "border border-line bg-surface")}
            >
              <span className={cn("font-display text-6xl leading-none", i === 1 ? "text-gold" : "text-clay")}>“</span>
              <blockquote className={cn("mt-2 flex-1 font-display text-xl leading-snug", i === 1 ? "text-white/90" : "text-ink")}>{t.q}</blockquote>
              <figcaption className="mt-6 flex items-center gap-3">
                <Avatar name={t.n} />
                <div>
                  <p className="text-sm font-bold">{t.n}</p>
                  <p className={cn("text-xs", i === 1 ? "text-white/55" : "text-muted")}>{t.r}</p>
                </div>
              </figcaption>
            </figure>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ───────── Tarifs ───────── */
export function PricingSection() {
  return (
    <section id="tarifs" className="scroll-mt-24 bg-surface-2/60 py-20 sm:py-28">
      <div className="container-page">
        <SectionTitle center eyebrow="Tarifs" title={<>Moins cher qu&apos;un mètre de bazin.</>} intro="Commencez gratuitement. Passez à la suite quand votre atelier grandit." />
        <div className="mt-12">
          <Pricing />
        </div>
      </div>
    </section>
  );
}

/* ───────── FAQ ───────── */
export function Faq() {
  const faqs = [
    { q: "Faut-il un ordinateur ?", a: "Non. Mansa Vibes est pensé d'abord pour le téléphone. Il s'installe sur l'écran d'accueil comme une application, sans passer par le Play Store. Il marche aussi sur ordinateur et tablette." },
    { q: "Et si je n'ai pas de connexion ?", a: "L'application garde vos dernières données sur le téléphone. Vous continuez à consulter vos clients et commandes, et tout se synchronise dès que le réseau revient." },
    { q: "L'assistant vocal comprend-il le wolof ou le bambara ?", a: "Il comprend le français parlé au quotidien, y compris mélangé de wolof, bambara ou dioula pour les mots courants (prix, jours, tenues). Vous vérifiez toujours avant d'enregistrer." },
    { q: "Comment je paie l'abonnement ?", a: "Par Wave, Orange Money ou carte bancaire, au mois ou à l'année. Pas d'engagement : vous arrêtez quand vous voulez et vous pouvez exporter vos données." },
    { q: "Mes données sont-elles en sécurité ?", a: "Vos données sont chiffrées, sauvegardées chaque jour et n'appartiennent qu'à vous. Chaque atelier est strictement séparé des autres." },
    { q: "Puis-je importer mon cahier actuel ?", a: "Oui : importez vos contacts du téléphone, un fichier Excel, ou dictez vos clients à l'assistant. Sur le plan Maison, on s'en charge pour vous." },
  ];
  return (
    <section id="faq" className="scroll-mt-24 py-20 sm:py-28">
      <div className="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
        <SectionTitle eyebrow="Questions" title={<>On vous répond.</>} intro="Une autre question ? Écrivez-nous sur WhatsApp, on répond en moins d'une heure." />
        <div className="divide-y divide-line border-y border-line">
          {faqs.map((f) => (
            <details key={f.q} className="group py-1">
              <summary className="flex cursor-pointer list-none items-center justify-between gap-4 py-4 text-lg font-bold text-ink [&::-webkit-details-marker]:hidden">
                {f.q}
                <span className="grid size-8 shrink-0 place-items-center rounded-full bg-surface-2 text-xl transition group-open:rotate-45 group-open:bg-gold group-open:text-gold-ink">+</span>
              </summary>
              <p className="pb-5 pr-12 leading-relaxed text-ink-soft">{f.a}</p>
            </details>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ───────── Appel final ───────── */
export function FinalCta() {
  return (
    <section className="px-3 pb-3 sm:px-4 sm:pb-4">
      <div className="relative mx-auto max-w-[90rem] overflow-hidden rounded-[2.5rem] bg-gold px-6 py-20 text-center text-gold-ink sm:rounded-[3rem] sm:py-28">
        <Bogolan className="text-gold-ink/10" id="cta-bogolan" />
        <div className="relative mx-auto max-w-3xl">
          <span className="mx-auto grid size-16 place-items-center rounded-full bg-night text-gold">
            <Mic className="size-7" />
          </span>
          <h2 className="mt-8 font-display text-5xl font-medium leading-[1] tracking-[-0.03em] sm:text-7xl">
            Le roi de l&apos;atelier,
            <br />
            <span className="italic">c&apos;est vous.</span>
          </h2>
          <p className="mx-auto mt-6 max-w-xl text-lg font-medium opacity-80">14 jours pour tout essayer. Sans carte, sans engagement, avec un vrai humain sur WhatsApp si besoin.</p>
          <div className="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
            <ButtonLink href="/inscription" variant="ink" size="lg">
              Ouvrir mon atelier <ArrowRight className="size-4" />
            </ButtonLink>
            <ButtonLink href="/app" size="lg" className="bg-white/40 text-gold-ink shadow-none hover:bg-white/60">
              Explorer la démo
            </ButtonLink>
          </div>
        </div>
      </div>
    </section>
  );
}

/* ───────── Pied de page ───────── */
export function Footer() {
  const cols = [
    { t: "Produit", l: [["Assistant vocal", "#assistant"], ["Fonctionnalités", "#fonctionnalites"], ["Tarifs", "#tarifs"], ["Démo", "/app"]] },
    { t: "Atelier", l: [["Créer un compte", "/inscription"], ["Connexion", "/connexion"], ["Suivi client", "/suivi/aw7k2p"]] },
    { t: "Aide", l: [["FAQ", "#faq"], ["WhatsApp", "#"], ["Confidentialité", "#"]] },
  ];
  return (
    <footer className="py-14">
      <div className="container-page">
        <div className="grid gap-10 md:grid-cols-[1.5fr_repeat(3,1fr)]">
          <div>
            <Logo />
            <p className="mt-4 max-w-xs text-sm text-muted">Le logiciel des ateliers de couture d&apos;Afrique de l&apos;Ouest. Pensé à Dakar, cousu pour toute la région.</p>
          </div>
          {cols.map((c) => (
            <div key={c.t}>
              <p className="text-xs font-bold uppercase tracking-widest text-muted">{c.t}</p>
              <ul className="mt-4 space-y-2.5">
                {c.l.map(([label, href]) => (
                  <li key={label}>
                    <Link href={href} className="text-sm font-semibold text-ink-soft hover:text-ink">
                      {label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
        <div className="mt-12 flex flex-col justify-between gap-3 border-t border-line pt-6 text-xs text-muted sm:flex-row">
          <p>© 2026 Mansa Vibes. Tous droits réservés.</p>
          <p>Fait avec ♥ pour les tailleurs, couturières et brodeurs.</p>
        </div>
      </div>
    </footer>
  );
}
