"use client";

import { motion } from "framer-motion";
import { Truck, RotateCcw, ShieldCheck, Headphones } from "lucide-react";
import { HeroCarousel } from "@/components/marketplace/hero-carousel";
import { CategoryCard } from "@/components/marketplace/category-card";
import { ProductCard } from "@/components/marketplace/product-card";
import { FlashDealStrip } from "@/components/marketplace/flash-deal-strip";
import { PromoBanner } from "@/components/marketplace/promo-banner";
import { TopSelection } from "@/components/marketplace/top-selection";
import { SectionHeader } from "@/components/marketplace/section-header";
import { MOCK_CATEGORIES, MOCK_PRODUCTS } from "@/lib/constants";

const promoBanners = [
  {
    id: 1,
    image: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=700&h=300&fit=crop",
    href: "/products?category=fashion",
    alt: "Summer Fashion Sale — Up to 60% Off",
  },
  {
    id: 2,
    image: "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=700&h=300&fit=crop",
    href: "/products?category=electronics",
    alt: "New Tech Arrivals — Latest Gadgets",
  },
];

const topSelections = [
  {
    id: 1,
    title: "Top Rated Fashion",
    discount: "Min. 50% Off",
    image: "https://images.unsplash.com/photo-1558171813-4c088753af8f?w=200&h=200&fit=crop",
    href: "/products?category=fashion",
  },
  {
    id: 2,
    title: "Best of Gadgets",
    discount: "Up to 60% Off",
    image: "https://placehold.co/200x200/e8ecf1/8d95a5?text=Gadgets",
    href: "/products?category=electronics",
  },
  {
    id: 3,
    title: "Premium Footwear",
    discount: "Min. 40% Off",
    image: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=200&h=200&fit=crop",
    href: "/products?category=footwear",
  },
];

const trustFeatures = [
  { icon: Truck, title: "Free Delivery", desc: "On orders over ₹499" },
  { icon: RotateCcw, title: "Easy Returns", desc: "30-day return policy" },
  { icon: ShieldCheck, title: "Secure Payments", desc: "100% protected checkout" },
  { icon: Headphones, title: "24/7 Support", desc: "We're here to help" },
];

export default function HomePage() {
  return (
    <div className="min-h-screen">
      {/* Hero */}
      <section className="pt-3 pb-2">
        <div className="container mx-auto px-4">
          <HeroCarousel />
        </div>
      </section>

      {/* Featured Categories */}
      <section className="py-6">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.15 }}
            className="bg-white rounded-2xl border border-border/60 py-6 px-4 shadow-sm"
          >
            <div className="flex items-center justify-between sm:justify-center gap-5 sm:gap-8 lg:gap-14 overflow-x-auto scrollbar-hide">
              {MOCK_CATEGORIES.map((category, i) => (
                <CategoryCard key={category.id} category={category} index={i} />
              ))}
            </div>
          </motion.div>
        </div>
      </section>

      {/* Flash Deals */}
      <section className="pb-4">
        <div className="container mx-auto px-4">
          <FlashDealStrip
            title="Best of Electronics"
            products={MOCK_PRODUCTS.slice(0, 6)}
            href="/products?category=electronics"
          />
        </div>
      </section>

      {/* Featured Products */}
      <section className="py-6">
        <div className="container mx-auto px-4">
          <div className="bg-white rounded-2xl border border-border/60 p-5 md:p-6 shadow-sm">
            <SectionHeader
              title="Suggested for You"
              subtitle="Handpicked products based on trending interests"
              href="/products"
            />
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
              {MOCK_PRODUCTS.map((product, i) => (
                <ProductCard key={product.id} product={product} index={i} />
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Promo Banners */}
      <section className="py-4">
        <div className="container mx-auto px-4">
          <PromoBanner banners={promoBanners} />
        </div>
      </section>

      {/* Fashion Deals */}
      <section className="py-6">
        <div className="container mx-auto px-4">
          <FlashDealStrip
            title="Fashion Deals"
            products={MOCK_PRODUCTS.slice(3, 9)}
            href="/products?category=fashion"
          />
        </div>
      </section>

      {/* Top Selection */}
      <section className="py-6">
        <div className="container mx-auto px-4">
          <SectionHeader
            title="Top Selection"
            subtitle="Curated picks at great prices"
          />
          <TopSelection items={topSelections} />
        </div>
      </section>

      {/* Trust Bar */}
      <section className="border-t border-border bg-white py-10 mt-4">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5 }}
            className="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12"
          >
            {trustFeatures.map((item, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 12 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1, duration: 0.4 }}
                className="text-center group"
              >
                <div className="w-12 h-12 mx-auto mb-3 rounded-2xl bg-primary-50 flex items-center justify-center group-hover:bg-primary-100 transition-colors duration-200">
                  <item.icon className="w-5.5 h-5.5 text-primary-600" />
                </div>
                <h3 className="font-semibold text-text-primary text-sm">
                  {item.title}
                </h3>
                <p className="text-xs text-text-muted mt-0.5">{item.desc}</p>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>
    </div>
  );
}
