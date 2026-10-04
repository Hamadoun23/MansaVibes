"use client";

import { useEffect } from "react";
import { FallbackScreen, fallbackPrimary, HomeLink } from "@/components/fallbacks";

/** Erreur dans l'application : la barre de navigation reste en place, seul le contenu est remplacé. */
export default function AppError({ error, retry }: { error: Error & { digest?: string }; retry: () => void }) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <FallbackScreen
      fabric="indigo"
      code={error.digest ? `Incident ${error.digest}` : "Incident"}
      title={
        <>
          Cet écran n&apos;a pas pu s&apos;ouvrir.
        </>
      }
      text="Vérifiez la connexion puis réessayez. Le reste de l'atelier fonctionne normalement."
      actions={
        <>
          <button type="button" onClick={() => retry()} className={fallbackPrimary}>
            Réessayer
          </button>
          <HomeLink href="/app">Aujourd&apos;hui</HomeLink>
        </>
      }
    />
  );
}
