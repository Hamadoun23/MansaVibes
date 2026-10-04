"use client";

import { useEffect } from "react";
import { FallbackScreen, fallbackPrimary, HomeLink } from "@/components/fallbacks";

export default function Error({ error, retry }: { error: Error & { digest?: string }; retry: () => void }) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <FallbackScreen
      fabric="indigo"
      code={error.digest ? `Incident ${error.digest}` : "Incident"}
      title={
        <>
          Un fil a cassé. <em className="italic text-clay">On recoud ?</em>
        </>
      }
      text="Quelque chose s'est mal passé de notre côté. Vos données ne sont pas perdues : réessayez dans un instant."
      actions={
        <>
          <button type="button" onClick={() => retry()} className={fallbackPrimary}>
            Réessayer
          </button>
          <HomeLink />
        </>
      }
    />
  );
}
