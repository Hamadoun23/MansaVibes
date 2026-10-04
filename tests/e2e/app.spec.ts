import { expect, test } from "@playwright/test";

test("navigation principale entre les écrans", async ({ page, isMobile }) => {
  await page.goto("/app");
  const nav = isMobile ? page.getByRole("navigation", { name: "Navigation principale" }) : page.locator("aside");
  await nav.getByRole("link", { name: "Commandes" }).click();
  await expect(page.locator("h1")).toHaveText("Commandes");
  await nav.getByRole("link", { name: "Clients" }).click();
  await expect(page.locator("h1")).toHaveText("Clients");
  await nav.getByRole("link", { name: "Caisse" }).click();
  await expect(page.locator("h1")).toHaveText("Caisse du jour");
});

test("commandes : filtre « En retard » et recherche", async ({ page }) => {
  await page.goto("/app/commandes");
  await page.getByRole("button", { name: /En retard/ }).click();
  await expect(page.getByRole("link", { name: /Cheikh Fall/ })).toBeVisible();
  await expect(page.getByRole("link", { name: /Mariama/ })).toHaveCount(0);

  await page.getByRole("button", { name: /En cours/ }).click();
  await page.getByPlaceholder(/Client, tenue/).fill("kaftan");
  await expect(page.getByRole("link", { name: /Ousmane Sarr/ })).toBeVisible();
  await expect(page.getByRole("link", { name: /Aminata/ })).toHaveCount(0);
});

test("commande : encaisser met à jour le reste à payer", async ({ page }) => {
  await page.goto("/app/commandes/o1");
  await expect(page.getByText("25 000 F").first()).toBeVisible();
  await page.getByRole("button", { name: "Encaisser" }).click();
  await page.getByLabel("Montant").fill("10000");
  await page.getByRole("button", { name: "Orange" }).click();
  await page.getByRole("button", { name: /Valider/ }).click();
  await expect(page.getByText("15 000 F").first()).toBeVisible();
  await expect(page.getByText("+10 000 F")).toBeVisible();
});

test("commande : faire avancer le statut", async ({ page }) => {
  await page.goto("/app/commandes/o1");
  await page.getByRole("button", { name: "Passer à « Prête »" }).click();
  await expect(page.getByRole("button", { name: "Passer à « Livrée »" })).toBeVisible();
  await expect(page.getByText(/votre boubou brodé est prêt/i)).toBeVisible();
});

test("assistant → paiement pré-rempli ouvert sur la commande", async ({ page }) => {
  await page.goto("/app/commandes/o10?encaisser=5000&mode=orange");
  await expect(page.getByLabel("Montant")).toHaveValue("5000");
  await expect(page.getByRole("button", { name: "Orange" })).toHaveAttribute("aria-pressed", "true");
});

test("assistant → statut proposé, annulable", async ({ page }) => {
  await page.goto("/app/commandes/o1?statut=prete");
  await expect(page.getByText(/par l'assistant/)).toBeVisible();
  await page.getByRole("button", { name: "Annuler" }).click();
  await expect(page.getByRole("button", { name: "Passer à « Prête »" })).toBeVisible();
});

test("clients : recherche et fiche avec mesures", async ({ page }) => {
  await page.goto("/app/clients?q=Khady");
  await expect(page.getByPlaceholder(/Nom, téléphone/)).toHaveValue("Khady");
  await page.getByRole("link", { name: /Khady Ndiaye/ }).click();
  await expect(page.locator("h1")).toHaveText("Khady Ndiaye");
  await expect(page.getByText("Tour de cou")).toBeVisible();
});
