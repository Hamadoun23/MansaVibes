import { expect, test } from "@playwright/test";

/** Toutes les pages s'ouvrent, sans erreur JavaScript ni débordement horizontal. */
const routes = [
  { path: "/", heading: /L'atelier/ },
  { path: "/connexion", heading: /Bon retour/ },
  { path: "/inscription", heading: /Ouvrez votre/ },
  { path: "/app", heading: /Bonjour Awa/ },
  { path: "/app/commandes", heading: /Commandes/ },
  { path: "/app/commandes/o1", heading: /Boubou brodé/ },
  { path: "/app/clients", heading: /Clients/ },
  { path: "/app/clients/c1", heading: /Aminata Diop/ },
  { path: "/app/caisse", heading: /Caisse du jour/ },
  { path: "/app/assistant", heading: /Dicter une commande/ },
  { path: "/suivi/aw7k2p", heading: /Votre tenue/ },
];

for (const r of routes) {
  test(`${r.path} s'affiche correctement`, async ({ page }) => {
    const errors: string[] = [];
    page.on("pageerror", (e) => errors.push(e.message));
    page.on("console", (m) => {
      if (m.type() === "error" && !m.text().includes("favicon")) errors.push(m.text());
    });

    const res = await page.goto(r.path);
    expect(res?.status()).toBe(200);
    await expect(page.locator("h1").first()).toContainText(r.heading);

    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(overflow, "pas de défilement horizontal").toBeLessThanOrEqual(1);
    expect(errors).toEqual([]);
  });
}

test("une page inconnue affiche la 404 maison", async ({ page }) => {
  const res = await page.goto("/nimporte-quoi");
  expect(res?.status()).toBe(404);
  await expect(page.getByRole("heading", { name: /filé/ })).toBeVisible();
  await expect(page.getByRole("link", { name: /Ouvrir l'atelier/ })).toBeVisible();
});

test("une commande ou un suivi inexistant donne une 404", async ({ page }) => {
  expect((await page.goto("/app/commandes/zzz"))?.status()).toBe(404);
  expect((await page.goto("/suivi/jeton-invalide"))?.status()).toBe(404);
});
