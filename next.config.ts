import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // développement : autoriser l'ouverture depuis un téléphone du réseau local (IP du PC)
  allowedDevOrigins: ["192.168.1.114"],
};

export default nextConfig;
