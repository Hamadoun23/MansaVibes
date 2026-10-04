import type { Metadata, Viewport } from "next";
import { Fraunces, Manrope } from "next/font/google";
import "./globals.css";

const fraunces = Fraunces({
  variable: "--font-fraunces",
  subsets: ["latin"],
  axes: ["SOFT", "opsz"],
  style: ["normal", "italic"],
});

const manrope = Manrope({
  variable: "--font-manrope",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: {
    default: "Mansa Vibes — le logiciel des ateliers de couture",
    template: "%s · Mansa Vibes",
  },
  description:
    "Clients, mesures, commandes, encaissements Wave et Orange Money, rappels WhatsApp et assistant vocal : Mansa Vibes fait tourner votre atelier de couture depuis votre téléphone.",
  applicationName: "Mansa Vibes",
  appleWebApp: { capable: true, title: "Mansa Vibes", statusBarStyle: "black-translucent" },
};

export const viewport: Viewport = {
  themeColor: [
    { media: "(prefers-color-scheme: light)", color: "#fbf7f0" },
    { media: "(prefers-color-scheme: dark)", color: "#0e0c1d" },
  ],
  viewportFit: "cover",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="fr" className={`${fraunces.variable} ${manrope.variable} antialiased`}>
      <body className="min-h-dvh">{children}</body>
    </html>
  );
}
