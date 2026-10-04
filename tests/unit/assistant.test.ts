import { describe, expect, it } from "vitest";
import { emptyIntent, intentJsonSchema, IntentSchema, type Intent } from "@/lib/assistant/intent";
import { findClient, findClientInText, resolveIntent } from "@/lib/assistant/resolve";

const intent = (o: Partial<Intent>): Intent => ({ ...emptyIntent, reply: "ok", ...o });

describe("schéma d'intention", () => {
  it("accepte des mesures nulles (réponse fréquente du modèle) et les remplace par []", () => {
    const parsed = IntentSchema.parse({ ...emptyIntent, measurements: null });
    expect(parsed.measurements).toEqual([]);
  });
  it("refuse une intention inconnue", () => {
    expect(IntentSchema.safeParse({ ...emptyIntent, intent: "delete_everything" }).success).toBe(false);
  });
  it("JSON Schema strict : tous les champs requis, objet fermé", () => {
    expect(intentJsonSchema.additionalProperties).toBe(false);
    expect([...intentJsonSchema.required].sort()).toEqual(Object.keys(intentJsonSchema.properties).sort());
    expect(intentJsonSchema.properties.measurements.items.additionalProperties).toBe(false);
  });
});

describe("findClient", () => {
  it("retrouve par prénom, nom complet, sans accents ni casse", () => {
    expect(findClient("Aminata")?.id).toBe("c1");
    expect(findClient("cheikh fall")?.id).toBe("c2");
    expect(findClient("Mariama Ba")?.id).toBe("c3");
    expect(findClient("madame Gueye")?.id).toBe("c7");
  });
  it("renvoie null si inconnu ou vide", () => {
    expect(findClient("Zeinab")).toBeNull();
    expect(findClient("")).toBeNull();
    expect(findClient(null)).toBeNull();
  });
});

describe("resolveIntent : la bonne action, le bon écran", () => {
  it("chat → simple réponse", () => {
    expect(resolveIntent(intent({ intent: "chat", reply: "Bonjour !" }))).toEqual({ kind: "reply", reply: "Bonjour !" });
  });

  it("create_order → formulaire, client existant reconnu", () => {
    const a = resolveIntent(intent({ intent: "create_order", client_name: "Aminata", garment: "Robe", price: 30000 }));
    expect(a.kind).toBe("order_form");
    if (a.kind !== "order_form") return;
    expect(a.clientId).toBe("c1");
    expect(a.draft.client_name).toBe("Aminata Diop");
    expect(a.draft.phone).toBe("77 612 34 56");
    expect(a.draft.price).toBe(30000);
  });

  it("create_order → nouveau client", () => {
    const a = resolveIntent(intent({ intent: "create_order", client_name: "Awa Sy", phone: "76 12 34 56" }));
    expect(a.kind === "order_form" && a.clientId).toBeNull();
  });

  it("record_payment → commande en cours, montant et moyen pré-remplis", () => {
    const a = resolveIntent(intent({ intent: "record_payment", client_name: "Cheikh", amount: 15000, payment_method: "orange" }));
    expect(a).toMatchObject({ kind: "navigate", href: "/app/commandes/o2?encaisser=15000&mode=orange" });
  });

  it("record_payment sans montant → propose le reste à payer", () => {
    const a = resolveIntent(intent({ intent: "record_payment", client_name: "Cheikh Fall" }));
    expect(a).toMatchObject({ kind: "navigate", href: "/app/commandes/o2?encaisser=45000" });
  });

  it("record_payment → client sans commande en cours : ouvre sa fiche", () => {
    const a = resolveIntent(intent({ intent: "record_payment", client_name: "Pape Mbaye", amount: 5000 }));
    expect(a).toMatchObject({ kind: "navigate", href: "/app/clients/c8" });
  });

  it("update_status → choisit la commande de la tenue citée", () => {
    const a = resolveIntent(intent({ intent: "update_status", client_name: "Mariama", garment: "ensemble", status: "coupe" }));
    expect(a).toMatchObject({ kind: "navigate", href: "/app/commandes/o9?statut=coupe" });
  });

  it("client introuvable → liste des clients filtrée", () => {
    const a = resolveIntent(intent({ intent: "update_status", client_name: "Zeinab", status: "prete" }));
    expect(a).toMatchObject({ kind: "navigate", href: "/app/clients?q=Zeinab" });
  });

  it("add_measurements → formulaire de mesures du client", () => {
    const a = resolveIntent(intent({ intent: "add_measurements", client_name: "Khady", measurements: [{ key: "taille", value: 78 }] }));
    expect(a).toMatchObject({ kind: "client_form", mode: "measurements", clientId: "c5" });
  });

  it("create_client d'un client existant → complète sa fiche", () => {
    const a = resolveIntent(intent({ intent: "create_client", client_name: "Ousmane Sarr" }));
    expect(a).toMatchObject({ kind: "client_form", mode: "measurements", clientId: "c4" });
  });

  it("find_client / find_order / open_page", () => {
    expect(resolveIntent(intent({ intent: "find_client", client_name: "Rokhaya" }))).toMatchObject({ href: "/app/clients/c7" });
    expect(resolveIntent(intent({ intent: "find_order", query: "wax" }))).toMatchObject({ href: "/app/commandes?q=wax" });
    expect(resolveIntent(intent({ intent: "open_page", page: "cash" }))).toMatchObject({ href: "/app/caisse" });
    expect(resolveIntent(intent({ intent: "open_page", page: null }))).toMatchObject({ href: "/app" });
  });
});

describe("filet de sécurité : nom de client oublié par le modèle", () => {
  it("retrouve un client cité dans la phrase", () => {
    expect(findClientInText("Montre-moi la fiche de Rokhaya")?.id).toBe("c7");
    expect(findClientInText("Ouvre la commande d'Abdoulaye")?.id).toBe("c6");
    expect(findClientInText("Bonjour tout le monde")).toBeNull();
  });
  it("resolveIntent utilise la transcription quand client_name est vide", () => {
    expect(resolveIntent(intent({ intent: "find_client" }), "la fiche de Rokhaya")).toMatchObject({ href: "/app/clients/c7" });
    expect(resolveIntent(intent({ intent: "find_order", query: "Abdoulaye" }))).toMatchObject({ href: "/app/commandes/o6" });
  });
});
