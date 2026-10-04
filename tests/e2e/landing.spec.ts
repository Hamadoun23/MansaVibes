import { expect, test } from "@playwright/test";

test("tarifs : le choix mensuel / annuel change le prix", async ({ page }) => {
  await page.goto("/#tarifs");
  const tarifs = page.locator("#tarifs");
  await expect(tarifs).toContainText("6 600 F");
  await expect(tarifs).toContainText("79 000 F par an");
  await page.getByRole("radio", { name: "Mensuel" }).click();
  await expect(tarifs).toContainText("7 900 F");
  await expect(tarifs).not.toContainText("6 600 F");
});

test("les appels à l'action mènent à l'inscription et à la démo", async ({ page }) => {
  await page.goto("/");
  await page.getByRole("link", { name: "Voir la démo" }).first().click();
  await expect(page).toHaveURL(/\/app$/);
  await page.goto("/");
  await page.getByRole("link", { name: /Ouvrir mon atelier/ }).first().click();
  await expect(page).toHaveURL(/\/inscription$/);
});

test("FAQ : une question s'ouvre au clic", async ({ page }) => {
  await page.goto("/#questions");
  const q = page.locator("details").filter({ hasText: "Et sans connexion ?" });
  await q.locator("summary").click();
  await expect(q.getByText(/synchronise quand le réseau revient/)).toBeVisible();
});

test("mobile : le menu plein écran s'ouvre et se ferme", async ({ page, isMobile }) => {
  test.skip(!isMobile, "menu réservé au mobile");
  await page.goto("/");
  await page.getByRole("button", { name: "Ouvrir le menu" }).click();
  const link = page.getByRole("link", { name: /Tarifs/ }).last();
  await expect(link).toBeVisible();
  await page.getByRole("button", { name: "Fermer le menu" }).click();
  await expect(page.getByRole("button", { name: "Fermer le menu" })).not.toBeInViewport();
});
