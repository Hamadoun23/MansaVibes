import { useId } from "react";
import { cn } from "@/lib/utils";

export type Fabric = "wax" | "bogolan" | "kente" | "bazin" | "indigo";

export const fabricNames: Record<Fabric, string> = {
  wax: "Wax",
  bogolan: "Bogolan",
  kente: "Kente",
  bazin: "Bazin riche",
  indigo: "Indigo adire",
};

/**
 * Étoffes dessinées en SVG : l'identité visuelle de Mansa Vibes.
 * Chaque motif est une interprétation simplifiée d'un textile d'Afrique de l'Ouest.
 */
export function Swatch({ fabric, className }: { fabric: Fabric; className?: string }) {
  const id = useId().replace(/:/g, "");
  const p = `${fabric}-${id}`;

  return (
    <svg aria-hidden className={cn("block h-full w-full", className)} preserveAspectRatio="xMidYMid slice" viewBox="0 0 400 500">
      <defs>
        {fabric === "wax" && (
          <pattern id={p} width="100" height="100" patternUnits="userSpaceOnUse">
            <rect width="100" height="100" fill="#c8461f" />
            <circle cx="50" cy="50" r="40" fill="#f2b233" />
            <circle cx="50" cy="50" r="29" fill="#1b2f6b" />
            <circle cx="50" cy="50" r="18" fill="#f6ead2" />
            <circle cx="50" cy="50" r="8" fill="#c8461f" />
            {[0, 100].map((x) => [0, 100].map((y) => <circle key={`${x}${y}`} cx={x} cy={y} r="11" fill="#1b2f6b" />))}
            <path d="M50 0v6M50 94v6M0 50h6M94 50h6" stroke="#f6ead2" strokeWidth="3" strokeLinecap="round" />
          </pattern>
        )}
        {fabric === "bogolan" && (
          <pattern id={p} width="120" height="120" patternUnits="userSpaceOnUse">
            <rect width="120" height="120" fill="#3b2516" />
            <rect x="4" y="4" width="52" height="52" fill="#e9dcc2" />
            <rect x="64" y="64" width="52" height="52" fill="#e9dcc2" />
            <path d="M10 18l8-8 8 8 8-8 8 8 8-8M10 34l8-8 8 8 8-8 8 8 8-8" fill="none" stroke="#3b2516" strokeWidth="3" />
            <g fill="#3b2516">
              {[0, 1, 2].map((i) => [0, 1, 2].map((j) => <circle key={`${i}${j}`} cx={76 + i * 14} cy={76 + j * 14} r="3" />))}
            </g>
            <path d="M70 20h40M90 6v28M74 10l32 20M106 10L74 30" stroke="#e9dcc2" strokeWidth="2.5" />
            <path d="M12 74h36v36H12z M22 84h16v16H22z" fill="none" stroke="#e9dcc2" strokeWidth="2.5" />
          </pattern>
        )}
        {fabric === "kente" && (
          <pattern id={p} width="160" height="80" patternUnits="userSpaceOnUse">
            <rect width="160" height="80" fill="#e8a523" />
            <rect y="0" width="160" height="8" fill="#14532d" />
            <rect y="16" width="160" height="4" fill="#9f1d16" />
            <rect y="60" width="160" height="4" fill="#9f1d16" />
            <rect y="72" width="160" height="8" fill="#14532d" />
            <rect x="0" y="22" width="40" height="36" fill="#111" />
            <rect x="80" y="22" width="40" height="36" fill="#14532d" />
            {[0, 1, 2, 3, 4].map((i) => (
              <rect key={i} x={6 + i * 7} y="28" width="3" height="24" fill="#e8a523" />
            ))}
            {[0, 1, 2].map((i) => (
              <rect key={i} x="86" y={28 + i * 9} width="28" height="3" fill="#e8a523" />
            ))}
            <path d="M48 40l12-12 12 12-12 12z" fill="#9f1d16" />
            <path d="M128 40l12-12 12 12-12 12z" fill="#111" />
          </pattern>
        )}
        {fabric === "bazin" && (
          <>
            <pattern id={p} width="80" height="80" patternUnits="userSpaceOnUse">
              <rect width="80" height="80" fill="#24346e" />
              <path d="M40 8c12 10 12 22 0 32-12-10-12-22 0-32zM40 40c12 10 12 22 0 32-12-10-12-22 0-32z" fill="#2f4386" />
              <path d="M8 40c10-12 22-12 32 0-10 12-22 12-32 0zM40 40c10-12 22-12 32 0-10 12-22 12-32 0z" fill="#2b3d7c" />
              <circle cx="40" cy="40" r="3" fill="#3d55a3" />
            </pattern>
            <linearGradient id={`${p}-sheen`} x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stopColor="#fff" stopOpacity="0" />
              <stop offset="0.45" stopColor="#fff" stopOpacity="0.22" />
              <stop offset="0.55" stopColor="#fff" stopOpacity="0.05" />
              <stop offset="1" stopColor="#fff" stopOpacity="0" />
            </linearGradient>
          </>
        )}
        {fabric === "indigo" && (
          <pattern id={p} width="90" height="90" patternUnits="userSpaceOnUse">
            <rect width="90" height="90" fill="#16204a" />
            <circle cx="45" cy="45" r="26" fill="none" stroke="#d9e1f2" strokeWidth="2" strokeDasharray="1 5" strokeLinecap="round" />
            <circle cx="45" cy="45" r="17" fill="none" stroke="#d9e1f2" strokeWidth="2" />
            <circle cx="45" cy="45" r="7" fill="#d9e1f2" />
            <path d="M0 0l12 12M90 0L78 12M0 90l12-12M90 90L78 78" stroke="#d9e1f2" strokeWidth="2" strokeLinecap="round" />
          </pattern>
        )}
      </defs>
      <rect width="400" height="500" fill={`url(#${p})`} />
      {fabric === "bazin" && <rect width="400" height="500" fill={`url(#${p}-sheen)`} />}
      {/* trame : légère texture de tissage */}
      <rect width="400" height="500" fill="url(#weave)" opacity="0.5" />
    </svg>
  );
}

/** Texture de tissage partagée, à placer une fois dans la page. */
export function WeaveDefs() {
  return (
    <svg aria-hidden width="0" height="0" className="absolute">
      <defs>
        <pattern id="weave" width="4" height="4" patternUnits="userSpaceOnUse">
          <path d="M0 0h4M0 2h4" stroke="#000" strokeOpacity="0.06" strokeWidth="1" />
          <path d="M1 0v4" stroke="#fff" strokeOpacity="0.05" strokeWidth="1" />
        </pattern>
      </defs>
    </svg>
  );
}
