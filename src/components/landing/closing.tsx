import Link from "next/link";
import { Label, RevealLines } from "./reveal";
import { Swatch, type Fabric } from "./swatch";

const faqs = [
  { q: "Faut-il un ordinateur ?", a: "Non. Mansa Vibes est pensé pour le téléphone et s'installe sur l'écran d'accueil sans passer par un store. Il marche aussi sur ordinateur et tablette." },
  { q: "Et sans connexion ?", a: "Vos dernières données restent sur le téléphone. Vous consultez clients et commandes, et tout se synchronise quand le réseau revient." },
  { q: "L'assistant comprend-il le wolof ou le bambara ?", a: "Il comprend le français parlé au quotidien, mêlé de wolof, bambara ou dioula pour les mots courants. Vous vérifiez toujours avant d'enregistrer." },
  { q: "Comment je paie l'abonnement ?", a: "Par Wave, Orange Money ou carte, au mois ou à l'année. Sans engagement, et vos données s'exportent à tout moment." },
  { q: "Mes données sont-elles protégées ?", a: "Elles sont chiffrées, sauvegardées chaque jour et n'appartiennent qu'à vous. Chaque atelier est séparé des autres." },
  { q: "Je peux importer mon cahier ?", a: "Oui : contacts du téléphone, fichier Excel, ou dictée à l'assistant. Avec le plan Maison, on s'en occupe pour vous." },
];

export function Faq() {
  return (
    <section id="questions" className="bg-bg px-4 py-28 sm:px-8 sm:py-36">
      <div className="mx-auto grid max-w-[90rem] gap-10 lg:grid-cols-[1fr_3fr]">
        <Label n="06" className="text-muted">
          Questions
        </Label>
        <div>
          <RevealLines
            className="font-display text-[clamp(2.4rem,5vw,4.5rem)] font-light leading-[0.95] tracking-[-0.03em] text-ink"
            lines={["Ce qu'on nous demande", <em key="e" className="italic text-clay">le plus souvent.</em>]}
          />
          <div className="mt-14 border-t border-ink">
            {faqs.map((f, i) => (
              <details key={f.q} className="group border-b border-ink/15">
                <summary className="flex cursor-pointer list-none items-baseline gap-6 py-6 [&::-webkit-details-marker]:hidden">
                  <span className="font-mono text-xs text-muted">{String(i + 1).padStart(2, "0")}</span>
                  <span className="flex-1 font-display text-2xl font-light text-ink sm:text-3xl">{f.q}</span>
                  <span className="font-mono text-lg text-ink transition-transform duration-300 group-open:rotate-45">+</span>
                </summary>
                <p className="max-w-2xl pb-8 pl-10 text-lg leading-relaxed text-ink-soft sm:pl-12">{f.a}</p>
              </details>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

const strip: Fabric[] = ["wax", "bogolan", "kente", "indigo", "bazin", "wax", "kente", "bogolan"];

export function Closing() {
  return (
    <footer className="overflow-hidden bg-night text-[#f6ead2]">
      {/* bande de tissus */}
      <div className="flex h-16 sm:h-24" aria-hidden>
        {strip.map((f, i) => (
          <div key={i} className="h-full flex-1">
            <Swatch fabric={f} />
          </div>
        ))}
      </div>

      <div className="px-4 pt-24 sm:px-8 sm:pt-36">
        <div className="mx-auto max-w-[90rem]">
          <RevealLines
            className="font-display text-[clamp(3rem,9vw,8.5rem)] font-light leading-[0.88] tracking-[-0.04em]"
            lines={["Le roi de l'atelier,", <em key="e" className="italic text-gold">c&apos;est vous.</em>]}
          />
          <div className="mt-12 flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
            <p className="max-w-md text-lg leading-relaxed text-[#f6ead2]/65">
              Quatorze jours pour tout essayer. Sans carte bancaire, sans engagement, avec quelqu&apos;un pour vous répondre sur WhatsApp.
            </p>
            <Link href="/inscription" className="group inline-flex h-16 items-center gap-4 self-start rounded-full bg-gold pl-8 pr-2 text-lg font-semibold text-gold-ink transition hover:bg-[#f6ead2]">
              Ouvrir mon atelier
              <span className="grid size-12 place-items-center rounded-full bg-night text-gold transition-transform group-hover:rotate-[-45deg]">→</span>
            </Link>
          </div>

          <div className="mt-28 grid gap-8 border-t border-[#f6ead2]/15 pt-10 font-mono text-[0.72rem] uppercase tracking-[0.12em] sm:grid-cols-4">
            <p className="text-[#f6ead2]/45">Pensé à Dakar, cousu pour toute l&apos;Afrique de l&apos;Ouest.</p>
            {[
              [
                ["Comment ça marche", "#histoire"],
                ["Fonctionnalités", "#atelier"],
                ["Tarifs", "#tarifs"],
              ],
              [
                ["Créer un compte", "/inscription"],
                ["Connexion", "/connexion"],
                ["Démo", "/app"],
              ],
              [
                ["Questions", "#questions"],
                ["Exemple de suivi", "/suivi/aw7k2p"],
                ["Confidentialité", "#"],
              ],
            ].map((col, i) => (
              <ul key={i} className="space-y-3">
                {col.map(([label, href]) => (
                  <li key={label}>
                    <Link href={href} className="text-[#f6ead2]/80 transition hover:text-gold">
                      {label}
                    </Link>
                  </li>
                ))}
              </ul>
            ))}
          </div>
        </div>
      </div>

      {/* logotype géant */}
      <p aria-hidden className="mt-16 select-none whitespace-nowrap text-center font-display text-[19.5vw] font-light leading-[0.72] tracking-[-0.06em] text-[#f6ead2]/[0.07]">
        Mansa Vibes
      </p>
      <div className="flex justify-between px-4 py-6 font-mono text-[0.65rem] uppercase tracking-[0.12em] text-[#f6ead2]/40 sm:px-8">
        <span>© 2026 Mansa Vibes</span>
        <span>Pour les tailleurs, couturières et brodeurs</span>
      </div>
    </footer>
  );
}
