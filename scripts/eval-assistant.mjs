// Banc d'essai de l'assistant IA : envoie des phrases réelles au serveur et vérifie l'action choisie.
// Usage : npm run eval:assistant            (serveur https://localhost:3001 par défaut)
//         EVAL_URL=http://localhost:3000 npm run eval:assistant
// Consomme ~25 requêtes du quota Groq gratuit.

process.env.NODE_TLS_REJECT_UNAUTHORIZED ??= "0"; // certificat local auto-signé
const base = process.env.EVAL_URL ?? "https://localhost:3001";
const today = "2026-10-04"; // dimanche
const pause = Number(process.env.EVAL_PAUSE_MS ?? 2500); // le palier gratuit de Groq limite les jetons par minute

/** kind attendu, et éventuellement un morceau de lien ou des champs. */
const cases = [
  // conversation
  { say: "Salut, comment tu vas ?", kind: "reply" },
  { say: "Tu peux faire quoi exactement ?", kind: "reply" },
  { say: "Quel temps fait-il à Dakar ?", kind: "reply" },
  // nouvelles commandes
  { say: "Nouvelle cliente Awa Sy 76 12 34 56, boubou en bazin bleu nuit pour samedi, 45 000, elle a donné 20 000 en Wave", kind: "order_form", draft: { client_name: /Awa Sy/, price: 45000, deposit: 20000, payment_method: "wave", due_date: "2026-10-10" } },
  { say: "Aminata veut une robe en wax pour vendredi, trente-cinq mille", kind: "order_form", clientId: "c1", draft: { price: 35000, due_date: "2026-10-09" } },
  { say: "kaftan lin beige pour Ousmane, 30 mille, livraison dans une semaine", kind: "order_form", clientId: "c4", draft: { price: 30000, due_date: "2026-10-11" } },
  { say: "Commande pour madame Gueye : camisole et pagne en bogolan, 22000 francs, avance 10000 en espèces", kind: "order_form", clientId: "c7", draft: { price: 22000, deposit: 10000, payment_method: "especes" } },
  // clients et mesures
  { say: "Enregistre un nouveau client Moussa Keita 77 445 12 90 à Ouakam", kind: "client_form", mode: "create" },
  { say: "Tour de taille 78 et poitrine 96 pour Khady", kind: "client_form", mode: "measurements", clientId: "c5", measures: { taille: 78, poitrine: 96 } },
  { say: "Les mesures de Cheikh : épaule 47, manche 64, longueur pantalon 104", kind: "client_form", clientId: "c2", measures: { epaule: 47, manche: 64, pantalon: 104 } },
  // paiements
  { say: "Aminata a payé 15 000 en Orange Money", kind: "navigate", href: /\/app\/commandes\/o(1|10)\?encaisser=15000&mode=orange/ },
  { say: "Cheikh Fall vient de verser 20 mille par Wave", kind: "navigate", href: /\/app\/commandes\/o2\?encaisser=20000&mode=wave/ },
  { say: "Khady a tout soldé en espèces", kind: "navigate", href: /\/app\/commandes\/o5\?encaisser=/ },
  // avancement
  { say: "La robe de Mariama est prête", kind: "navigate", href: /\/app\/commandes\/o3\?statut=prete/ },
  { say: "J'ai livré le grand boubou de Cheikh", kind: "navigate", href: /\/app\/commandes\/o2\?statut=livree/ },
  { say: "On a commencé la coupe du kaftan d'Ousmane", kind: "navigate", href: /\/app\/commandes\/o4\?statut=coupe/ },
  // recherche et écrans
  { say: "Montre-moi la fiche de Rokhaya", kind: "navigate", href: /\/app\/clients\/c7/ },
  { say: "C'est quoi le numéro de Pape Mbaye ?", kind: "navigate", href: /\/app\/clients\/c8/ },
  { say: "Ouvre la commande d'Abdoulaye", kind: "navigate", href: /\/app\/commandes\/o6/ },
  { say: "Montre-moi la caisse", kind: "navigate", href: /^\/app\/caisse$/ },
  { say: "Qu'est-ce qui est en retard aujourd'hui ?", kind: "navigate", href: /^\/app(\/commandes.*)?$/ },
  { say: "Toutes les commandes en wax", kind: "navigate", href: /\/app\/commandes\?q=/ },
  // client inconnu : on ne doit pas inventer
  { say: "Zeinab a payé 5000", kind: "navigate", href: /\/app\/clients\?q=Zeinab/i },
  // wolof / transcription approximative
  { say: "Aminata dafa fey 10 000 ci vague", kind: "navigate", href: /encaisser=10000&mode=wave/ },
  { say: "Awa dafa fey 10 000 ci vague pour son boubou", kind: "navigate", href: /\/app\/clients\?q=Awa/ }, // Awa n'est pas cliente : on ne doit rien inventer
];

function check(c, action) {
  const problems = [];
  if (action.kind !== c.kind) problems.push(`action ${action.kind} ≠ ${c.kind}`);
  if (c.href && !(action.href && c.href.test(action.href))) problems.push(`lien ${action.href ?? "—"} ≠ ${c.href}`);
  if (c.clientId !== undefined && action.clientId !== c.clientId) problems.push(`client ${action.clientId} ≠ ${c.clientId}`);
  if (c.mode && action.mode !== c.mode) problems.push(`mode ${action.mode} ≠ ${c.mode}`);
  for (const [k, v] of Object.entries(c.draft ?? {})) {
    const got = action.draft?.[k];
    const ok = v instanceof RegExp ? v.test(String(got ?? "")) : got === v;
    if (!ok) problems.push(`${k}=${JSON.stringify(got)} ≠ ${v}`);
  }
  for (const [k, v] of Object.entries(c.measures ?? {})) {
    const got = action.measurements?.find((m) => m.key === k)?.value;
    if (got !== v) problems.push(`mesure ${k}=${got} ≠ ${v}`);
  }
  return problems;
}

let passed = 0;
let local = 0;
const started = Date.now();
for (const c of cases) {
  await new Promise((r) => setTimeout(r, pause));
  const t0 = Date.now();
  let res;
  try {
    const r = await fetch(`${base}/api/assistant`, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ transcript: c.say, today }) });
    res = await r.json();
  } catch (e) {
    console.log(`✗ ${c.say}\n    serveur injoignable : ${e.message}`);
    continue;
  }
  if (res.source !== "llm") local++;
  const problems = check(c, res.action);
  const ms = Date.now() - t0;
  if (!problems.length) passed++;
  const mark = problems.length ? (c.optional ? "~" : "✗") : "✓";
  console.log(`${mark} ${c.say}  (${ms} ms${res.source !== "llm" ? ", repli local" : ""})`);
  if (problems.length) console.log(`    ${problems.join(" · ")}\n    → ${JSON.stringify(res.action).slice(0, 220)}`);
}
const strict = cases.filter((c) => !c.optional).length;
console.log(`\n${passed}/${cases.length} réussis (${strict} obligatoires) · ${local} en repli local · ${((Date.now() - started) / 1000).toFixed(1)} s`);
if (local) console.log("⚠ Des réponses viennent du repli local : GROQ_API_KEY absente ou erreur Groq (voir les logs du serveur).");
