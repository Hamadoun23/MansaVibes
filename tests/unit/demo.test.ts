import { describe, expect, it } from "vitest";
import { clientById, clients, orders, payments, todayStats } from "@/lib/demo";

describe("données de démonstration (cohérence)", () => {
  it("chaque commande pointe vers un client existant et a un jeton unique", () => {
    for (const o of orders) expect(clientById(o.clientId), o.ref).toBeDefined();
    expect(new Set(orders.map((o) => o.token)).size).toBe(orders.length);
  });
  it("aucun acompte ne dépasse le prix", () => {
    for (const o of orders) expect(o.paid, o.ref).toBeLessThanOrEqual(o.price);
  });
  it("chaque paiement pointe vers une commande", () => {
    for (const p of payments) expect(orders.some((o) => o.id === p.orderId)).toBe(true);
  });
  it("les identifiants clients sont uniques", () => {
    expect(new Set(clients.map((c) => c.id)).size).toBe(clients.length);
  });
  it("statistiques du jour", () => {
    const s = todayStats();
    expect(s.late.map((o) => o.ref).sort()).toEqual(["MV-1033", "MV-1041"]);
    expect(s.cashedToday).toBe(90000);
    expect(s.ready).toHaveLength(2);
  });
});
