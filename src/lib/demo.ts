/**
 * Données de démonstration. Elles alimentent la landing et l'application en attendant l'API.
 * Les échéances sont exprimées en jours relatifs (0 = aujourd'hui) pour rester toujours « fraîches ».
 */

export type OrderStatus = "nouvelle" | "coupe" | "couture" | "finitions" | "prete" | "livree";
export type PayMethod = "wave" | "orange" | "especes" | "carte";

export const statusMeta: Record<OrderStatus, { label: string; tone: "gold" | "clay" | "leaf" | "sky" | "neutral"; step: number }> = {
  nouvelle: { label: "Nouvelle", tone: "neutral", step: 0 },
  coupe: { label: "Coupe", tone: "sky", step: 1 },
  couture: { label: "Couture", tone: "sky", step: 2 },
  finitions: { label: "Finitions", tone: "gold", step: 3 },
  prete: { label: "Prête", tone: "leaf", step: 4 },
  livree: { label: "Livrée", tone: "neutral", step: 5 },
};

export const statusOrder: OrderStatus[] = ["nouvelle", "coupe", "couture", "finitions", "prete", "livree"];

export const payMethodLabel: Record<PayMethod, string> = {
  wave: "Wave",
  orange: "Orange Money",
  especes: "Espèces",
  carte: "Carte",
};

export type Measurement = { key: string; label: string; value: number };

export type Client = {
  id: string;
  name: string;
  phone: string;
  city: string;
  since: string;
  notes?: string;
  measurements: Measurement[];
};

export type Order = {
  id: string;
  ref: string;
  clientId: string;
  garment: string;
  fabric: string;
  status: OrderStatus;
  dueIn: number;
  price: number;
  paid: number;
  tailor: string;
  urgent?: boolean;
  token: string;
};

export type Payment = { id: string; orderId: string; amount: number; method: PayMethod; when: string };

export const atelier = {
  name: "Atelier Awa Couture",
  city: "Dakar, Sicap Liberté",
  phone: "+221 77 412 08 33",
  owner: "Awa Ndiaye",
  plan: "Atelier",
  team: [
    { name: "Awa Ndiaye", role: "Gérante" },
    { name: "Moussa Diallo", role: "Tailleur" },
    { name: "Fatou Sow", role: "Brodeuse" },
    { name: "Ibrahima Ba", role: "Apprenti" },
  ],
};

const m = (values: Record<string, number>): Measurement[] =>
  Object.entries(values).map(([key, value]) => ({ key, label: measurementLabels[key] ?? key, value }));

export const measurementLabels: Record<string, string> = {
  cou: "Tour de cou",
  epaule: "Épaule",
  poitrine: "Poitrine",
  taille: "Taille",
  bassin: "Bassin",
  manche: "Longueur manche",
  bras: "Tour de bras",
  poignet: "Poignet",
  longueur: "Longueur totale",
  pantalon: "Longueur pantalon",
  cuisse: "Tour de cuisse",
};

export const clients: Client[] = [
  { id: "c1", name: "Aminata Diop", phone: "77 612 34 56", city: "Mermoz", since: "2023", notes: "Préfère les cols montants. Toujours pressée.", measurements: m({ cou: 36, epaule: 39, poitrine: 94, taille: 76, bassin: 102, manche: 58, bras: 30, longueur: 142 }) },
  { id: "c2", name: "Cheikh Fall", phone: "76 201 77 10", city: "Plateau", since: "2024", measurements: m({ cou: 41, epaule: 47, poitrine: 104, taille: 92, manche: 64, poignet: 18, longueur: 150, pantalon: 104, cuisse: 60 }) },
  { id: "c3", name: "Mariama Bâ", phone: "78 990 12 45", city: "Point E", since: "2022", notes: "Mariage de sa sœur le mois prochain : 3 tenues assorties.", measurements: m({ cou: 34, epaule: 37, poitrine: 88, taille: 70, bassin: 98, manche: 56, longueur: 140 }) },
  { id: "c4", name: "Ousmane Sarr", phone: "77 455 66 02", city: "Ouakam", since: "2025", measurements: m({ cou: 43, epaule: 49, poitrine: 110, taille: 98, manche: 65, longueur: 152, pantalon: 106 }) },
  { id: "c5", name: "Khady Ndiaye", phone: "70 118 40 90", city: "Liberté 6", since: "2021", measurements: m({ cou: 35, epaule: 38, poitrine: 92, taille: 74, bassin: 100, manche: 57, longueur: 138 }) },
  { id: "c6", name: "Abdoulaye Kane", phone: "77 300 21 87", city: "Almadies", since: "2024", notes: "Client fidèle, paie toujours en Wave.", measurements: m({ cou: 40, epaule: 46, poitrine: 102, taille: 88, manche: 63, longueur: 148, pantalon: 102 }) },
  { id: "c7", name: "Rokhaya Gueye", phone: "78 645 93 11", city: "Grand Yoff", since: "2025", measurements: m({ cou: 35, epaule: 38, poitrine: 96, taille: 80, bassin: 106, manche: 57, longueur: 140 }) },
  { id: "c8", name: "Pape Mbaye", phone: "76 777 01 23", city: "HLM", since: "2023", measurements: m({ cou: 42, epaule: 48, poitrine: 106, taille: 94, manche: 64, longueur: 150, pantalon: 105 }) },
];

export const orders: Order[] = [
  { id: "o1", ref: "MV-1042", clientId: "c1", garment: "Boubou brodé", fabric: "Bazin riche bleu nuit", status: "finitions", dueIn: 0, price: 45000, paid: 20000, tailor: "Moussa Diallo", urgent: true, token: "aw7k2p" },
  { id: "o2", ref: "MV-1041", clientId: "c2", garment: "Grand boubou 3 pièces", fabric: "Getzner blanc cassé", status: "couture", dueIn: -2, price: 85000, paid: 40000, tailor: "Moussa Diallo", urgent: true, token: "cf9x1d" },
  { id: "o3", ref: "MV-1040", clientId: "c3", garment: "Robe de soirée", fabric: "Wax Vlisco « Fleurs de mariage »", status: "prete", dueIn: 1, price: 38000, paid: 38000, tailor: "Fatou Sow", token: "mb3q8z" },
  { id: "o4", ref: "MV-1039", clientId: "c4", garment: "Kaftan", fabric: "Lin sable", status: "coupe", dueIn: 4, price: 30000, paid: 15000, tailor: "Ibrahima Ba", token: "os5t0r" },
  { id: "o5", ref: "MV-1038", clientId: "c5", garment: "Ensemble taille-basse", fabric: "Wax jaune soleil", status: "prete", dueIn: 0, price: 25000, paid: 10000, tailor: "Fatou Sow", token: "kn2w4e" },
  { id: "o6", ref: "MV-1037", clientId: "c6", garment: "Costume africain", fabric: "Bazin gris perle", status: "nouvelle", dueIn: 9, price: 60000, paid: 30000, tailor: "Moussa Diallo", token: "ak8m6y" },
  { id: "o7", ref: "MV-1036", clientId: "c7", garment: "Camisole + pagne", fabric: "Bogolan ocre", status: "couture", dueIn: 3, price: 22000, paid: 22000, tailor: "Fatou Sow", token: "rg1v7u" },
  { id: "o8", ref: "MV-1035", clientId: "c8", garment: "Tunique brodée", fabric: "Coton damassé", status: "livree", dueIn: -5, price: 28000, paid: 28000, tailor: "Ibrahima Ba", token: "pm4c9i" },
  { id: "o9", ref: "MV-1034", clientId: "c3", garment: "Ensemble demoiselle d'honneur", fabric: "Wax Vlisco « Fleurs de mariage »", status: "nouvelle", dueIn: 12, price: 32000, paid: 0, tailor: "Fatou Sow", token: "mb6h2k" },
  { id: "o10", ref: "MV-1033", clientId: "c1", garment: "Jupe portefeuille", fabric: "Wax indigo", status: "couture", dueIn: -1, price: 15000, paid: 5000, tailor: "Ibrahima Ba", token: "aw1n5b" },
];

export const payments: Payment[] = [
  { id: "p1", orderId: "o1", amount: 20000, method: "wave", when: "09:12" },
  { id: "p2", orderId: "o5", amount: 10000, method: "orange", when: "10:40" },
  { id: "p3", orderId: "o3", amount: 18000, method: "wave", when: "11:05" },
  { id: "p4", orderId: "o6", amount: 30000, method: "especes", when: "14:22" },
  { id: "p5", orderId: "o7", amount: 12000, method: "wave", when: "16:47" },
];

export const weekRevenue = [
  { day: "Lun", amount: 62000 },
  { day: "Mar", amount: 48000 },
  { day: "Mer", amount: 91000 },
  { day: "Jeu", amount: 55000 },
  { day: "Ven", amount: 120000 },
  { day: "Sam", amount: 143000 },
  { day: "Dim", amount: 90000 },
];

export const clientById = (id: string) => clients.find((c) => c.id === id);
export const orderById = (id: string) => orders.find((o) => o.id === id);
export const orderByToken = (token: string) => orders.find((o) => o.token === token);
export const ordersOf = (clientId: string) => orders.filter((o) => o.clientId === clientId);
export const paymentsOf = (orderId: string) => payments.filter((p) => p.orderId === orderId);

export const todayStats = () => {
  const active = orders.filter((o) => o.status !== "livree");
  return {
    late: active.filter((o) => o.dueIn < 0 && o.status !== "prete"),
    dueToday: active.filter((o) => o.dueIn === 0),
    ready: active.filter((o) => o.status === "prete"),
    cashedToday: payments.reduce((s, p) => s + p.amount, 0),
    outstanding: active.reduce((s, o) => s + (o.price - o.paid), 0),
    activeCount: active.length,
  };
};
