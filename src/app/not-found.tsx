import { FallbackScreen, HomeLink } from "@/components/fallbacks";
import { ButtonLink } from "@/components/ui";

export default function NotFound() {
  return (
    <FallbackScreen
      code="Erreur 404"
      title={
        <>
          Cette page a filé <span className="italic text-clay">entre les doigts.</span>
        </>
      }
      text="Le lien est peut-être ancien, ou la commande a été supprimée."
      actions={
        <>
          <ButtonLink href="/app">Ouvrir l&apos;atelier</ButtonLink>
          <HomeLink />
        </>
      }
    />
  );
}
