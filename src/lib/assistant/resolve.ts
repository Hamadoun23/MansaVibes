import { clients, orders, statusMeta, type Client, type Order } from "@/lib/demo";
import type { OrderDraft } from "@/lib/order-draft";
import { fcfa } from "@/lib/utils";
import type { Intent } from "./intent";

/** Ce que l'écran assistant doit faire après avoir compris la demande. */
export type AssistantAction =
  | { kind: "reply"; reply: string }
  | { kind: "order_form"; reply: string; draft: OrderDraft; clientId: string | null }
  | {
      kind: "client_form";
      reply: string;
      mode: "create" | "measurements";
      clientId: string | null;
      client: { name: string; phone: string; city: string };
      measurements: { key: string; value: number }[];
    }
  | { kind: "navigate"; reply: string; href: string; label: string };

const normalize = (s: string) =>
  s
    .normalize("NFD")
    .replace(/[̀-ͯ]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9 ]/g, " ")
    .trim();

/** Retrouve un client à partir d'un nom prononcé (« Aminata », « madame Diop », « Cheikh Fall »). */
export function findClient(spoken: string | null): Client | null {
  if (!spoken) return null;
  const words = normalize(spoken).split(/\s+/).filter((w) => w.length > 1 && !["madame", "monsieur", "mme", "mr"].includes(w));
  if (!words.length) return null;
  const scored = clients
    .map((c) => {
      const tokens = normalize(c.name).split(/\s+/);
      const hits = words.filter((w) => tokens.some((t) => t === w || (w.length > 3 && t.startsWith(w)))).length;
      return { c, hits };
    })
    .filter((x) => x.hits > 0)
    .sort((a, b) => b.hits - a.hits);
  if (!scored.length) return null;
  // ambigu si deux clients ont le même score (ex. deux « Ndiaye »)
  if (scored.length > 1 && scored[0].hits === scored[1].hits) return null;
  return scored[0].c;
}

/** Commande en cours la plus pertinente d'un client (on préfère celle dont la tenue est citée). */
function activeOrderOf(client: Client, garment: string | null): Order | null {
  const active = orders.filter((o) => o.clientId === client.id && o.status !== "livree");
  if (garment) {
    const g = normalize(garment);
    const match = active.find((o) => normalize(o.garment).includes(g) || g.includes(normalize(o.garment)));
    if (match) return match;
  }
  return active.sort((a, b) => a.dueIn - b.dueIn)[0] ?? null;
}

const pageHref = { today: "/app", orders: "/app/commandes", clients: "/app/clients", cash: "/app/caisse" } as const;
const pageLabel = { today: "Aujourd'hui", orders: "Commandes", clients: "Clients", cash: "Caisse" } as const;

export function resolveIntent(i: Intent): AssistantAction {
  const client = findClient(i.client_name);
  const firstName = (client?.name ?? i.client_name ?? "").split(" ")[0];
  const notFound = (what: string): AssistantAction => ({
    kind: "navigate",
    reply: `Je ne retrouve pas ${i.client_name ? `« ${i.client_name} »` : "le client"} pour ${what}. Choisissez-le dans la liste.`,
    href: `/app/clients${i.client_name ? `?q=${encodeURIComponent(i.client_name)}` : ""}`,
    label: "Clients",
  });

  switch (i.intent) {
    case "create_order":
      return {
        kind: "order_form",
        reply: i.reply,
        clientId: client?.id ?? null,
        draft: {
          client_name: client?.name ?? i.client_name,
          phone: client?.phone ?? i.phone,
          garment: i.garment,
          fabric: i.fabric,
          due_date: i.due_date,
          price: i.price,
          deposit: i.deposit,
          payment_method: i.payment_method,
          notes: i.notes,
        },
      };

    case "create_client":
    case "add_measurements": {
      if (i.intent === "add_measurements" && !client) return notFound("ajouter les mesures");
      return {
        kind: "client_form",
        reply: client && i.intent === "create_client" ? `${client.name} existe déjà : je complète sa fiche.` : i.reply,
        mode: client ? "measurements" : "create",
        clientId: client?.id ?? null,
        client: { name: client?.name ?? i.client_name ?? "", phone: client?.phone ?? i.phone ?? "", city: client?.city ?? i.city ?? "" },
        measurements: i.measurements,
      };
    }

    case "record_payment": {
      if (!client) return notFound("encaisser");
      const order = activeOrderOf(client, i.garment);
      if (!order) return { kind: "navigate", reply: `${firstName} n'a pas de commande en cours. J'ouvre sa fiche.`, href: `/app/clients/${client.id}`, label: client.name };
      const params = new URLSearchParams({ encaisser: String(i.amount ?? order.price - order.paid) });
      if (i.payment_method) params.set("mode", i.payment_method);
      return {
        kind: "navigate",
        reply: i.amount ? `J'ouvre l'encaissement de ${fcfa(i.amount)} pour ${firstName} (${order.garment.toLowerCase()}).` : i.reply,
        href: `/app/commandes/${order.id}?${params}`,
        label: `${order.ref} · ${order.garment}`,
      };
    }

    case "update_status": {
      if (!client) return notFound("mettre à jour la commande");
      const order = activeOrderOf(client, i.garment);
      if (!order) return { kind: "navigate", reply: `${firstName} n'a pas de commande en cours. J'ouvre sa fiche.`, href: `/app/clients/${client.id}`, label: client.name };
      const status = i.status ?? "prete";
      return {
        kind: "navigate",
        reply: `Je passe la commande « ${order.garment} » de ${firstName} en « ${statusMeta[status].label} ». Vérifiez, puis prévenez le client.`,
        href: `/app/commandes/${order.id}?statut=${status}`,
        label: `${order.ref} · ${order.garment}`,
      };
    }

    case "find_client":
      if (client) return { kind: "navigate", reply: i.reply || `Voici la fiche de ${client.name}.`, href: `/app/clients/${client.id}`, label: client.name };
      return { kind: "navigate", reply: i.reply, href: `/app/clients${i.client_name ? `?q=${encodeURIComponent(i.client_name)}` : ""}`, label: "Clients" };

    case "find_order": {
      if (client) {
        const order = activeOrderOf(client, i.garment);
        if (order) return { kind: "navigate", reply: i.reply, href: `/app/commandes/${order.id}`, label: `${order.ref} · ${order.garment}` };
      }
      const q = i.query ?? i.client_name ?? i.garment;
      return { kind: "navigate", reply: i.reply, href: `/app/commandes${q ? `?q=${encodeURIComponent(q)}` : ""}`, label: "Commandes" };
    }

    case "open_page": {
      const page = i.page ?? "today";
      return { kind: "navigate", reply: i.reply, href: pageHref[page], label: pageLabel[page] };
    }

    default:
      return { kind: "reply", reply: i.reply || "Je peux créer une commande, un client, noter des mesures, encaisser ou faire avancer une commande. Dites-moi !" };
  }
}
