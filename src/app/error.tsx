"use client";

import { useEffect } from "react";
import { FallbackScreen, HomeLink } from "@/components/fallbacks";
import { Button } from "@/components/ui";

export default function Error({ error, retry }: { error: Error & { digest?: string }; retry: () => void }) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <FallbackScreen
      code={error.digest ? `Incident ${error.digest}` : "Incident"}
      title={
        <>
          Un fil a cassé. <span className="italic text-clay">On recoud ?</span>
        </>
      }
      text="Quelque chose s'est mal passé de notre côté. Vos données ne sont pas perdues : réessayez dans un instant."
      actions={
        <>
          <Button onClick={() => retry()}>Réessayer</Button>
          <HomeLink />
        </>
      }
    />
  );
}
