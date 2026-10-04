# Mansa Vibes

Le logiciel des ateliers de couture d'Afrique de l'Ouest : clients et mesures, commandes, encaissements
Wave / Orange Money / espèces, messages WhatsApp, lien de suivi pour le client — et un **assistant vocal**
qui comprend ce que dit le tailleur et ouvre le bon écran, champs déjà remplis.

> Statut : démo fonctionnelle (données de démonstration en mémoire). La base de données arrive ensuite.

## Pile

- **Next.js 16** (App Router) · **React 19** · **TypeScript** · **Tailwind CSS 4**
- **Assistant** : dictée dans le navigateur (Web Speech API, `fr-FR`) → route `/api/assistant` →
  **Groq** (`openai/gpt-oss-120b`, sortie JSON stricte) → intention + champs → redirection vers le bon écran
- Tests : **Vitest** (logique) · **Playwright** (parcours mobile + ordinateur)

## Démarrer en local

```bash
npm install
cp .env.example .env.local   # puis renseigner GROQ_API_KEY (gratuit : https://console.groq.com/keys)
npm run dev                  # http://localhost:3000
```

Sans clé Groq, l'application fonctionne : une analyse simple par mots-clés prend le relais pour les commandes.

### Tester depuis un téléphone du réseau local

Safari (iPhone) n'autorise le micro qu'en HTTPS. Générer un certificat pour l'IP du PC dans `certificates/`
(ignoré par Git), l'ajouter à `allowedDevOrigins` dans `next.config.ts`, puis :

```bash
npx next dev -H 0.0.0.0 --experimental-https \
  --experimental-https-key certificates/dev-key.pem --experimental-https-cert certificates/dev-cert.pem
```

## Ce que comprend l'assistant

| Exemple | Action |
|---|---|
| « Nouvelle cliente Awa, boubou bazin pour samedi, 45 000, acompte 20 000 en Wave » | formulaire de commande pré-rempli |
| « Tour de taille 78 et poitrine 96 pour Khady » | formulaire de mesures |
| « Aminata a payé 15 000 en Orange Money » | la commande, encaissement prêt |
| « La robe de Mariama est prête » | la commande, statut proposé (annulable) |
| « Montre-moi la caisse » | écran Caisse |

Rien n'est enregistré sans validation. Les dates citées (« vendredi », « demain ») sont calculées côté serveur.

## Tests

```bash
npm test                 # tests unitaires (Vitest)
npm run test:e2e         # parcours navigateur sur build de production (Playwright)
npm run eval:assistant   # banc d'essai de l'IA réelle (≈ 25 requêtes Groq, serveur lancé)
```

## Structure

```
src/app/              pages (landing, /app, /suivi/[jeton], /api/assistant)
src/components/       interface (landing, app, ui)
src/lib/assistant/    schéma d'intention, instructions du modèle, résolution vers les écrans
src/lib/demo.ts       données de démonstration
tests/                unit/ et e2e/
scripts/              banc d'essai de l'assistant
```
