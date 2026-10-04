import { describe, expect, it } from "vitest";
import { dueLabel, fcfa, initials } from "@/lib/utils";

describe("fcfa", () => {
  it("groupe les milliers avec une espace fine insécable", () => {
    expect(fcfa(45000)).toBe("45 000 F");
    expect(fcfa(1250000)).toBe("1 250 000 F");
  });
  it("gère zéro, les petits montants, les négatifs et l'arrondi", () => {
    expect(fcfa(0)).toBe("0 F");
    expect(fcfa(500)).toBe("500 F");
    expect(fcfa(-2500)).toBe("-2 500 F");
    expect(fcfa(999.6)).toBe("1 000 F");
  });
  it("accepte un suffixe personnalisé", () => {
    expect(fcfa(7900, "")).toBe("7 900");
  });
});

describe("dueLabel", () => {
  it.each([
    [0, "Aujourd'hui"],
    [1, "Demain"],
    [4, "Dans 4 j"],
    [-1, "En retard d'1 j"],
    [-3, "En retard de 3 j"],
  ])("%i → %s", (days, label) => expect(dueLabel(days)).toBe(label));
});

describe("initials", () => {
  it("prend deux initiales en majuscules", () => {
    expect(initials("aminata diop")).toBe("AD");
    expect(initials("Cheikh  Fall Junior")).toBe("CF");
    expect(initials("Awa")).toBe("A");
  });
});
