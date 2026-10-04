import { Hero } from "@/components/landing/hero";
import { Nav } from "@/components/landing/nav";
import {
  Assistant,
  CityMarquee,
  ClientSpace,
  Faq,
  Features,
  FinalCta,
  Footer,
  PricingSection,
  Problems,
  Steps,
  Testimonials,
} from "@/components/landing/sections";

export default function Home() {
  return (
    <>
      <Nav />
      <main>
        <Hero />
        <CityMarquee />
        <Problems />
        <Assistant />
        <Features />
        <ClientSpace />
        <Steps />
        <Testimonials />
        <PricingSection />
        <Faq />
        <FinalCta />
      </main>
      <Footer />
    </>
  );
}
