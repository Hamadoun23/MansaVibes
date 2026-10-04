import { z } from "zod";

/** Brouillon de commande extrait d'une note vocale. Tous les champs sont optionnels : on n'invente rien. */
export const OrderDraftSchema = z.object({
  client_name: z.string().nullable().describe("Nom du client tel que prononcé, ou null"),
  phone: z.string().nullable().describe("Numéro de téléphone en chiffres groupés (ex. 76 12 34 56), ou null"),
  garment: z.string().nullable().describe("Type de tenue (boubou, kaftan, robe, ensemble…), ou null"),
  fabric: z.string().nullable().describe("Tissu et couleur (bazin bleu nuit, wax…), ou null"),
  due_date: z.string().nullable().describe("Date de livraison au format AAAA-MM-JJ, ou null"),
  price: z.number().int().nullable().describe("Prix total en francs CFA, ou null"),
  deposit: z.number().int().nullable().describe("Acompte versé en francs CFA, ou null"),
  payment_method: z.enum(["wave", "orange", "especes", "carte"]).nullable().describe("Moyen de paiement de l'acompte, ou null"),
  notes: z.string().nullable().describe("Autres détails utiles (modèle, broderie, urgence…), ou null"),
});

export type OrderDraft = z.infer<typeof OrderDraftSchema>;

export const emptyDraft: OrderDraft = {
  client_name: null,
  phone: null,
  garment: null,
  fabric: null,
  due_date: null,
  price: null,
  deposit: null,
  payment_method: null,
  notes: null,
};

const days = ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"];
const garments = ["grand boubou", "boubou", "kaftan", "caftan", "robe", "ensemble", "costume", "tunique", "camisole", "jupe", "pagne", "chemise", "pantalon", "taille-basse", "taille basse", "abacost", "djellaba"];
const fabrics = ["bazin", "wax", "getzner", "lin", "soie", "coton", "bogolan", "dentelle", "satin", "brocart", "voile", "kente", "damassé"];

function isoDate(d: Date) {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

/**
 * Analyse de secours, sans IA : repère téléphone, montants, jour, tenue, tissu et moyen de paiement.
 * Utilisée quand l'assistant IA (Groq) n'est pas configuré ou ne répond pas.
 */
export function parseDraftLocally(text: string, today = new Date()): OrderDraft {
  let t = text.toLowerCase();
  const draft: OrderDraft = { ...emptyDraft };

  const phone = text.match(/(?:\+?221\s?)?\b(7\d)[\s.-]?(\d{2,3})[\s.-]?(\d{2})[\s.-]?(\d{2,3})\b/);
  if (phone) {
    draft.phone = [phone[1], phone[2], phone[3], phone[4]].join(" ");
    // le numéro est masqué (même longueur) pour ne pas être lu comme un montant
    const start = phone.index ?? 0;
    t = t.slice(0, start) + " ".repeat(phone[0].length) + t.slice(start + phone[0].length);
  }

  const name = text.match(/\b(?:client(?:e)?|madame|monsieur|mme|m\.)\s+([A-ZÀ-Ý][\p{L}'-]+(?:\s+[A-ZÀ-Ý][\p{L}'-]+)?)/u)
    ?? text.match(/\bpour\s+([A-ZÀ-Ý][\p{L}'-]+(?:\s+[A-ZÀ-Ý][\p{L}'-]+)?)/u);
  if (name) draft.client_name = name[1];

  draft.garment = garments.find((g) => t.includes(g)) ?? null;
  if (draft.garment) draft.garment = draft.garment[0].toUpperCase() + draft.garment.slice(1);

  const fabric = fabrics.find((f) => t.includes(f));
  if (fabric) {
    const m = t.match(new RegExp(`${fabric}(?:\\s+(?!pour|à|a|de|le|la|et)[a-zéèêàç-]+){0,2}`));
    draft.fabric = m ? m[0][0].toUpperCase() + m[0].slice(1) : fabric;
  }

  // montants : « 45 000 », « 45000 », « 45 mille »
  const amounts: { value: number; index: number }[] = [];
  for (const m of t.matchAll(/(\d{1,3}(?:[\s. ]\d{3})+|\d{4,7})(?:\s*(?:f|francs|fcfa))?/g)) {
    amounts.push({ value: Number(m[1].replace(/\D/g, "")), index: m.index ?? 0 });
  }
  for (const m of t.matchAll(/(\d{1,3})\s*mille/g)) amounts.push({ value: Number(m[1]) * 1000, index: m.index ?? 0 });
  amounts.sort((a, b) => a.index - b.index);
  const depositWord = t.search(/acompte|avance|donné|versé|payé/);
  for (const a of amounts) {
    if (depositWord >= 0 && a.index > depositWord && draft.deposit === null) draft.deposit = a.value;
    else if (draft.price === null) draft.price = a.value;
  }

  if (/\bwave\b/.test(t)) draft.payment_method = "wave";
  else if (/orange|\bom\b/.test(t)) draft.payment_method = "orange";
  else if (/espèce|espece|cash|liquide/.test(t)) draft.payment_method = "especes";
  else if (/carte/.test(t)) draft.payment_method = "carte";

  if (/après-demain|apres-demain/.test(t)) draft.due_date = isoDate(new Date(today.getTime() + 2 * 864e5));
  else if (/demain/.test(t)) draft.due_date = isoDate(new Date(today.getTime() + 864e5));
  else {
    const day = days.findIndex((d) => t.includes(d));
    if (day >= 0) {
      const delta = ((day - today.getDay() + 7) % 7) || 7;
      draft.due_date = isoDate(new Date(today.getTime() + delta * 864e5));
    }
  }
  return draft;
}
