export function cn(...classes: Array<string | false | null | undefined>) {
  return classes.filter(Boolean).join(" ");
}

/** 45000 → « 45 000 F » — formatage manuel pour un rendu identique serveur / navigateur. */
export function fcfa(amount: number, suffix = " F") {
  const sign = amount < 0 ? "-" : "";
  const digits = Math.abs(Math.round(amount)).toString();
  return sign + digits.replace(/\B(?=(\d{3})+(?!\d))/g, " ") + suffix;
}

export function initials(name: string) {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]!.toUpperCase())
    .join("");
}

/** Échéance relative : 0 = aujourd'hui, négatif = en retard. */
export function dueLabel(days: number) {
  if (days === 0) return "Aujourd'hui";
  if (days === 1) return "Demain";
  if (days === -1) return "En retard d'1 j";
  if (days < 0) return `En retard de ${-days} j`;
  return `Dans ${days} j`;
}
