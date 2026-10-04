import Link from "next/link";
import { FallbackScreen, fallbackPrimary, HomeLink } from "@/components/fallbacks";

export default function NotFound() {
  return (
    <FallbackScreen
      fabric="bogolan"
      code="Erreur 404"
      title={
        <>
          Cette page a filé <em className="italic text-clay">entre les doigts.</em>
        </>
      }
      text="Le lien est peut-être ancien, ou la commande a été supprimée."
      actions={
        <>
          <Link href="/app" className={fallbackPrimary}>
            Ouvrir l&apos;atelier
          </Link>
          <HomeLink />
        </>
      }
    />
  );
}
