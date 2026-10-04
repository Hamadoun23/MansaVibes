import { z } from "zod";
import { measurementLabels } from "@/lib/demo";

/**
 * Ce que le LLM renvoie : une intention (quel écran / quelle action) + les champs entendus.
 * Le même schéma sert au mode strict de Groq (JSON Schema) et à la validation côté serveur (zod).
 */

export const intents = [
  "create_order", // nouvelle commande (crée le client s'il n'existe pas)
  "create_client", // nouveau client, éventuellement avec mesures
  "add_measurements", // mesures pour un client existant
  "record_payment", // encaisser un paiement sur une commande
  "update_status", // faire avancer une commande (coupe, couture, prête, livrée…)
  "find_client", // ouvrir / chercher un client
  "find_order", // ouvrir / chercher une commande
  "open_page", // aller sur un écran (aujourd'hui, commandes, clients, caisse)
  "chat", // salutation, question, demande hors périmètre
] as const;

export const statuses = ["nouvelle", "coupe", "couture", "finitions", "prete", "livree"] as const;
export const payMethods = ["wave", "orange", "especes", "carte"] as const;
export const pages = ["today", "orders", "clients", "cash"] as const;
export const measurementKeys = Object.keys(measurementLabels) as [string, ...string[]];

export const IntentSchema = z.object({
  intent: z.enum(intents),
  reply: z.string(),
  client_name: z.string().nullable(),
  phone: z.string().nullable(),
  city: z.string().nullable(),
  garment: z.string().nullable(),
  fabric: z.string().nullable(),
  due_date: z.string().nullable(),
  price: z.number().nullable(),
  deposit: z.number().nullable(),
  amount: z.number().nullable(),
  payment_method: z.enum(payMethods).nullable(),
  status: z.enum(statuses).nullable(),
  // le modèle renvoie parfois null quand il n'y a pas de mesures
  measurements: z
    .array(z.object({ key: z.enum(measurementKeys), value: z.number() }))
    .nullable()
    .transform((m) => m ?? []),
  page: z.enum(pages).nullable(),
  query: z.string().nullable(),
  notes: z.string().nullable(),
});

export type Intent = z.infer<typeof IntentSchema>;

const nullable = (type: "string" | "number") => ({ type: [type, "null"] });
const nullableEnum = (values: readonly string[]) => ({ type: ["string", "null"], enum: [...values, null] });

/** JSON Schema écrit à la main : le mode strict de Groq veut des types-tableaux pour null. */
export const intentJsonSchema = {
  type: "object",
  additionalProperties: false,
  required: Object.keys(IntentSchema.shape),
  properties: {
    intent: { type: "string", enum: [...intents] },
    reply: { type: "string", description: "Réponse courte en français à afficher à l'utilisateur" },
    client_name: nullable("string"),
    phone: nullable("string"),
    city: nullable("string"),
    garment: nullable("string"),
    fabric: nullable("string"),
    due_date: { ...nullable("string"), description: "AAAA-MM-JJ" },
    price: nullable("number"),
    deposit: nullable("number"),
    amount: nullable("number"),
    payment_method: nullableEnum(payMethods),
    status: nullableEnum(statuses),
    measurements: {
      type: ["array", "null"],
      items: {
        type: "object",
        additionalProperties: false,
        required: ["key", "value"],
        properties: { key: { type: "string", enum: measurementKeys }, value: { type: "number" } },
      },
    },
    page: nullableEnum(pages),
    query: nullable("string"),
    notes: nullable("string"),
  },
} as const;

export const emptyIntent: Intent = {
  intent: "chat",
  reply: "",
  client_name: null,
  phone: null,
  city: null,
  garment: null,
  fabric: null,
  due_date: null,
  price: null,
  deposit: null,
  amount: null,
  payment_method: null,
  status: null,
  measurements: [],
  page: null,
  query: null,
  notes: null,
};

export const SYSTEM_PROMPT = `Tu es l'assistant de Mansa Vibes, logiciel d'un atelier de couture en Afrique de l'Ouest.
Le tailleur te parle (note vocale transcrite automatiquement, donc avec des fautes). Tu dois :
1. comprendre ce qu'il veut faire et choisir UNE intention ;
2. remplir les champs que tu as entendus ; tout le reste vaut null (n'invente jamais).

Intentions :
- create_order : il décrit une nouvelle commande (tenue, prix, date, acompte…).
- create_client : il veut enregistrer un nouveau client, sans commande.
- add_measurements : il donne des mesures pour un client (« tour de taille 76 pour Aminata »).
- record_payment : un client a payé ou versé de l'argent sur sa commande → amount + payment_method.
- update_status : une commande avance (« la robe de Mariama est prête », « j'ai livré Cheikh ») → status.
- find_client : il veut voir un client, ses mesures, son numéro.
- find_order : il cherche une commande ou une liste de commandes (query = mots utiles).
- open_page : il veut un écran : today (aujourd'hui, retards, à livrer), orders, clients, cash (caisse, encaissements).
- chat : salutation, question générale, ou demande que le logiciel ne gère pas.

Règles :
- reply : une phrase courte, chaleureuse, en français, qui dit ce que tu fais (ex. « Je prépare la commande d'Awa. »). Pour chat, réponds simplement et rappelle ce que tu sais faire si utile.
- La transcription peut être mêlée de wolof, bambara ou dioula. Corrige les erreurs évidentes (« bas un » → bazin, « vague » → Wave, « orange mané » → Orange Money).
- Montants en francs CFA entiers (« quarante-cinq mille » → 45000 ; « 45 » pour un prix de tenue → 45000).
- Dates relatives → AAAA-MM-JJ à partir de la date du jour fournie.
- Mesures en centimètres ; clés autorisées : ${measurementKeys.map((k) => `${k} (${measurementLabels[k]})`).join(", ")}.
- Noms propres avec majuscules. Téléphone en chiffres groupés par deux (76 12 34 56).
- Statuts : nouvelle, coupe, couture, finitions, prete, livree.`;
