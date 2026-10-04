import type { MetadataRoute } from "next";

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "Mansa Vibes",
    short_name: "Mansa Vibes",
    description: "Le logiciel des ateliers de couture.",
    start_url: "/app",
    display: "standalone",
    background_color: "#fbf7f0",
    theme_color: "#14112a",
    lang: "fr",
    icons: [{ src: "/icon.svg", sizes: "any", type: "image/svg+xml" }],
  };
}
