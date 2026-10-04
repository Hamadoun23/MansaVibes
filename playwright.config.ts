import { defineConfig, devices } from "@playwright/test";

/**
 * Tests de bout en bout.
 * Par défaut : build de production servi sur le port 3100.
 * E2E_BASE_URL=https://localhost:3001 pour viser un serveur déjà lancé.
 */
const external = process.env.E2E_BASE_URL;
const baseURL = external ?? "http://localhost:3100";

export default defineConfig({
  testDir: "tests/e2e",
  timeout: 45_000,
  expect: { timeout: 8_000 },
  fullyParallel: true,
  retries: process.env.CI ? 1 : 0,
  reporter: [["list"]],
  use: { baseURL, ignoreHTTPSErrors: true, trace: "retain-on-failure", locale: "fr-FR" },
  projects: [
    { name: "ordinateur", use: { ...devices["Desktop Chrome"], viewport: { width: 1440, height: 900 } } },
    { name: "mobile", use: { ...devices["Pixel 7"] } },
  ],
  webServer: external
    ? undefined
    : { command: "npm run build && npx next start -p 3100", url: baseURL, reuseExistingServer: true, timeout: 240_000, env: { GROQ_API_KEY: "" } },
});
