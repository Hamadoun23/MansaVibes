import { Catalogue } from "@/components/landing/catalogue";
import { Closing, Faq } from "@/components/landing/closing";
import { Hero } from "@/components/landing/hero";
import { Manifesto } from "@/components/landing/manifesto";
import { Nav } from "@/components/landing/nav";
import { Pricing } from "@/components/landing/pricing";
import { SmoothScroll } from "@/components/landing/smooth-scroll";
import { Story } from "@/components/landing/story";
import { WeaveDefs } from "@/components/landing/swatch";
import { Tracking } from "@/components/landing/tracking";

export default function Home() {
  return (
    <SmoothScroll>
      <div className="theme-light bg-bg text-ink">
        <WeaveDefs />
        <Nav />
        <main>
          <Hero />
          <Manifesto />
          <Story />
          <Catalogue />
          <Tracking />
          <Pricing />
          <Faq />
        </main>
        <Closing />
      </div>
    </SmoothScroll>
  );
}
