import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // production : serveur autonome pour l'image Docker du VPS
  output: "standalone",
  // développement : autoriser l'ouverture depuis un téléphone du réseau local (IP du PC)
  allowedDevOrigins: ["192.168.1.114"],
};

export default nextConfig;
