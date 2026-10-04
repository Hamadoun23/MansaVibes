import { Check, MessageCircle, Mic } from "lucide-react";
import { cn } from "@/lib/utils";

/** Cadre de téléphone en CSS pur. */
export function PhoneFrame({ children, className }: { children: React.ReactNode; className?: string }) {
  return (
    <div className={cn("relative w-[17rem] rounded-[2.9rem] bg-[#0b0a14] p-2 shadow-[0_50px_100px_-40px_rgba(20,17,42,0.7)] ring-1 ring-black/40 sm:w-[19rem]", className)}>
      <div className="absolute left-1/2 top-3.5 z-20 h-6 w-24 -translate-x-1/2 rounded-full bg-black" />
      <div className="relative h-[35rem] overflow-hidden rounded-[2.4rem] bg-[#fbf7f0] text-[#17142e] sm:h-[39rem]">{children}</div>
    </div>
  );
}

function ScreenHead({ over, title }: { over: string; title: string }) {
  return (
    <div className="px-5 pb-3 pt-12">
      <p className="font-mono text-[0.6rem] uppercase tracking-[0.14em] text-[#6e6880]">{over}</p>
      <p className="mt-0.5 font-display text-xl font-semibold">{title}</p>
    </div>
  );
}

/* 01 — Dicter */
export function ScreenDictate() {
  return (
    <div className="flex h-full flex-col">
      <ScreenHead over="Assistant" title="Je vous écoute…" />
      <div className="mx-5 mt-4 rounded-2xl bg-white p-4 ring-1 ring-[#e6dccb]">
        <p className="font-display text-[1.05rem] leading-snug">
          « Nouvelle cliente Awa Sy, 76 12 34 56. Boubou en bazin bleu nuit pour samedi, quarante-cinq mille.
          <span className="text-[#6e6880]"> Elle a donné vingt mille en Wave…</span> »
        </p>
      </div>
      <div className="mx-auto mt-auto mb-10 grid place-items-center">
        <div className="flex h-10 w-48 items-center gap-[3px]">
          {[3, 6, 9, 5, 8, 12, 7, 10, 4, 9, 11, 6, 8, 5, 10, 7, 4, 8, 6, 3].map((h, i) => (
            <span key={i} className="flex-1 rounded-full bg-[#c4512f]" style={{ height: `${h * 8}%` }} />
          ))}
        </div>
        <span className="mt-6 grid size-20 place-items-center rounded-full bg-[#c4512f] text-white">
          <span className="size-6 rounded-md bg-white" />
        </span>
      </div>
    </div>
  );
}

/* 02 — Vérifier */
export function ScreenReview() {
  const rows = [
    ["Cliente", "Awa Sy", "nouvelle"],
    ["Téléphone", "76 12 34 56"],
    ["Tenue", "Boubou · bazin bleu nuit"],
    ["Livraison", "Samedi 10 oct."],
    ["Prix", "45 000 F"],
    ["Acompte", "20 000 F · Wave"],
  ];
  return (
    <div className="flex h-full flex-col">
      <ScreenHead over="Nouvelle commande" title="À vérifier" />
      <dl className="mx-5 divide-y divide-[#e6dccb] rounded-2xl bg-white px-4 ring-1 ring-[#e6dccb]">
        {rows.map(([k, v, tag]) => (
          <div key={k} className="flex items-center justify-between gap-2 py-2.5">
            <dt className="text-[0.7rem] font-semibold text-[#6e6880]">{k}</dt>
            <dd className="text-right text-[0.8rem] font-bold">
              {v}
              {tag && <span className="ml-1.5 rounded-full bg-[#d5efe1] px-1.5 py-0.5 text-[0.6rem] text-[#1d8556]">{tag}</span>}
            </dd>
          </div>
        ))}
      </dl>
      <div className="mx-5 mt-3 flex justify-between rounded-2xl bg-[#14112a] px-4 py-3 text-white">
        <span className="text-xs text-white/60">Reste à payer</span>
        <span className="font-display font-semibold">25 000 F</span>
      </div>
      <span className="mx-5 mb-8 mt-auto flex h-12 items-center justify-center gap-2 rounded-full bg-[#e3a33a] text-sm font-bold text-[#2b1b02]">
        <Check className="size-4" /> Valider
      </span>
    </div>
  );
}

/* 03 — Coudre */
export function ScreenSew() {
  const steps = ["Commande reçue", "Coupe", "Couture", "Finitions", "Prête"];
  return (
    <div className="flex h-full flex-col">
      <ScreenHead over="MV-1042 · Moussa" title="Boubou d'Awa" />
      <ol className="mx-5 rounded-2xl bg-white p-4 ring-1 ring-[#e6dccb]">
        {steps.map((s, i) => (
          <li key={s} className="flex items-center gap-3 py-2">
            <span
              className={cn(
                "grid size-7 place-items-center rounded-full text-[0.65rem] font-bold",
                i < 3 ? "bg-[#1d8556] text-white" : i === 3 ? "bg-[#e3a33a] text-[#2b1b02]" : "bg-[#ece3d3] text-[#6e6880]",
              )}
            >
              {i < 3 ? <Check className="size-3.5" /> : i + 1}
            </span>
            <span className={cn("text-sm", i <= 3 ? "font-bold" : "text-[#6e6880]")}>{s}</span>
            {i === 3 && <span className="ml-auto font-mono text-[0.6rem] uppercase text-[#c4512f]">en cours</span>}
          </li>
        ))}
      </ol>
      <p className="mx-5 mt-3 rounded-2xl bg-[#f4ede1] p-3 text-xs leading-relaxed text-[#3a3552]">
        Broderie dorée au col. Prévoir 3 boutons de rechange.
      </p>
    </div>
  );
}

/* 04 — Livrer & encaisser */
export function ScreenDeliver() {
  return (
    <div className="flex h-full flex-col bg-[#efe7dc]">
      <div className="flex items-center gap-2.5 bg-[#075e54] px-4 pb-3 pt-11 text-white">
        <span className="grid size-8 place-items-center rounded-full bg-white/20 text-xs font-bold">AS</span>
        <div>
          <p className="text-sm font-bold">Awa Sy</p>
          <p className="text-[0.65rem] opacity-70">en ligne</p>
        </div>
      </div>
      <div className="space-y-2 p-3">
        <p className="ml-auto max-w-[85%] rounded-lg rounded-tr-none bg-[#d9fdd3] px-3 py-2 text-[0.8rem] leading-snug">
          Bonjour Awa, votre boubou est prêt ✨ Reste à régler : 25 000 F. Suivi : mansa.app/s/aw7k2p
          <span className="block text-right text-[0.6rem] text-[#667781]">10:42 ✓✓</span>
        </p>
        <p className="max-w-[80%] rounded-lg rounded-tl-none bg-white px-3 py-2 text-[0.8rem]">
          Merci ! Je viens d&apos;envoyer par Wave 🙏
          <span className="block text-right text-[0.6rem] text-[#667781]">10:51</span>
        </p>
      </div>
      <div className="mx-3 mt-auto mb-6 flex items-center gap-3 rounded-2xl bg-white p-3 shadow-lg">
        <span className="grid size-10 place-items-center rounded-full bg-[#1dc8f2] text-white">
          <MessageCircle className="size-4" />
        </span>
        <div className="flex-1">
          <p className="text-[0.7rem] text-[#6e6880]">Wave reçu · commande soldée</p>
          <p className="font-display text-lg font-semibold text-[#1d8556]">+25 000 F</p>
        </div>
        <Mic className="size-4 text-[#6e6880]" />
      </div>
    </div>
  );
}
