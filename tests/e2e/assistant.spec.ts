import { expect, test, type Page } from "@playwright/test";

/** L'API est simulée : on teste l'interface, pas le modèle (voir scripts/eval-assistant.mjs pour le modèle). */
async function mockAssistant(page: Page, action: object) {
  await page.route("**/api/assistant", (route) => route.fulfill({ json: { action, source: "llm" } }));
}

async function ask(page: Page, text: string) {
  await page.goto("/app/assistant");
  const typing = page.getByRole("button", { name: "Écrire plutôt" });
  if (await typing.isVisible().catch(() => false)) await typing.click();
  await page.getByRole("textbox").fill(text);
  await page.getByRole("button", { name: "Envoyer" }).click();
}

test("réponse simple (salutation)", async ({ page }) => {
  await mockAssistant(page, { kind: "reply", reply: "Je vais bien, merci !" });
  await ask(page, "Salut");
  await expect(page.getByText("Je vais bien, merci !")).toBeVisible();
});

test("nouvelle commande → formulaire pré-rempli et validation", async ({ page }) => {
  await mockAssistant(page, {
    kind: "order_form",
    reply: "Je prépare la commande d'Awa Sy.",
    clientId: null,
    draft: { client_name: "Awa Sy", phone: "76 12 34 56", garment: "Boubou", fabric: "Bazin", due_date: "2026-10-10", price: 45000, deposit: 20000, payment_method: "wave", notes: null },
  });
  await ask(page, "Nouvelle cliente Awa Sy…");
  await expect(page.getByLabel("Client")).toHaveValue("Awa Sy");
  await expect(page.getByLabel("Prix")).toHaveValue("45000");
  await expect(page.getByText("25 000 F")).toBeVisible();
  await expect(page.getByText("Nouveau client")).toBeVisible();
  await page.getByRole("button", { name: /Valider et enregistrer/ }).click();
  await expect(page.getByText("Commande enregistrée")).toBeVisible();
});

test("mesures → formulaire avec les mesures entendues", async ({ page }) => {
  await mockAssistant(page, {
    kind: "client_form",
    reply: "J'ajoute les mesures de Khady.",
    mode: "measurements",
    clientId: "c5",
    client: { name: "Khady Ndiaye", phone: "70 118 40 90", city: "Liberté 6" },
    measurements: [{ key: "taille", value: 78 }],
  });
  await ask(page, "taille 78 pour Khady");
  await expect(page.getByLabel("Taille")).toHaveValue("78");
  await expect(page.getByText(/déjà client/)).toBeVisible();
});

test("redirection vers le bon écran", async ({ page }) => {
  await mockAssistant(page, { kind: "navigate", reply: "J'ouvre la caisse.", href: "/app/caisse", label: "Caisse" });
  await ask(page, "Montre-moi la caisse");
  await expect(page).toHaveURL(/\/app\/caisse$/);
  await expect(page.locator("h1")).toHaveText("Caisse du jour");
});

test("serveur injoignable → message clair, pas d'écran cassé", async ({ page }) => {
  await page.route("**/api/assistant", (route) => route.fulfill({ status: 500, body: "boom" }));
  await ask(page, "Bonjour");
  await expect(page.getByRole("alert").filter({ hasText: /Impossible de joindre le serveur/ })).toBeVisible();
  await expect(page.getByRole("button", { name: "Envoyer" })).toBeVisible();
});

test("API réelle sans clé : repli local fonctionnel", async ({ request }) => {
  const res = await request.post("/api/assistant", { data: { transcript: "Boubou pour Awa samedi 45 000", today: "2026-10-04" } });
  expect(res.ok()).toBe(true);
  const body = await res.json();
  expect(["llm", "local"]).toContain(body.source);
  expect(body.action.kind).toBeTruthy();
});

test("API : transcription vide refusée proprement", async ({ request }) => {
  const res = await request.post("/api/assistant", { data: { transcript: "   " } });
  expect(res.status()).toBe(400);
});
