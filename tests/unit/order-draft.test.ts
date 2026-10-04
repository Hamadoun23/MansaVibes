import { describe, expect, it } from "vitest";
import { parseDraftLocally } from "@/lib/order-draft";

// dimanche 4 octobre 2026, midi
const today = new Date("2026-10-04T12:00:00");

describe("parseDraftLocally (analyse de secours)", () => {
  it("extrait une commande complète", () => {
    const d = parseDraftLocally("Nouvelle cliente Awa Sy, 76 12 34 56. Boubou en bazin bleu nuit pour samedi, 45 000. Elle a donné 20 000 en Wave.", today);
    expect(d).toMatchObject({
      client_name: "Awa Sy",
      phone: "76 12 34 56",
      garment: "Boubou",
      fabric: "Bazin bleu nuit",
      due_date: "2026-10-10",
      price: 45000,
      deposit: 20000,
      payment_method: "wave",
    });
  });

  it("comprend « mille », Orange Money et demain", () => {
    const d = parseDraftLocally("Kaftan pour Ousmane demain, 30 mille, avance de 10 mille par orange money", today);
    expect(d.garment).toBe("Kaftan");
    expect(d.price).toBe(30000);
    expect(d.deposit).toBe(10000);
    expect(d.payment_method).toBe("orange");
    expect(d.due_date).toBe("2026-10-05");
  });

  it("le même jour de semaine renvoie à la semaine suivante", () => {
    expect(parseDraftLocally("robe pour dimanche", today).due_date).toBe("2026-10-11");
  });

  it("ne confond pas le téléphone avec un prix", () => {
    const d = parseDraftLocally("client Moussa 77 412 08 33 tunique 28000", today);
    expect(d.phone).toBe("77 412 08 33");
    expect(d.price).toBe(28000);
  });

  it("renvoie des champs vides pour une phrase sans commande", () => {
    const d = parseDraftLocally("Salut, comment tu vas ?", today);
    expect(d.garment).toBeNull();
    expect(d.price).toBeNull();
    expect(d.phone).toBeNull();
  });
});
