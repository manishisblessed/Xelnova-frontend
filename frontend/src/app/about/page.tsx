"use client";

import { motion } from "framer-motion";
import { Users, Globe, Award, TrendingUp } from "lucide-react";

const stats = [
  { icon: Users, value: "10M+", label: "Happy Customers" },
  { icon: Globe, value: "500+", label: "Seller Partners" },
  { icon: Award, value: "50K+", label: "Products Listed" },
  { icon: TrendingUp, value: "99.9%", label: "Uptime" },
];

export default function AboutPage() {
  return (
    <div className="min-h-screen">
      <div className="bg-gradient-to-br from-primary-700 to-primary-900 text-white py-20">
        <div className="container mx-auto px-4 text-center">
          <motion.h1 initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} className="text-3xl md:text-5xl font-extrabold mb-4">About Xelnova</motion.h1>
          <motion.p initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }} className="text-white/70 max-w-2xl mx-auto text-lg">
            India&apos;s trusted marketplace connecting millions of buyers with quality sellers. We believe in making online shopping accessible, affordable, and delightful for everyone.
          </motion.p>
        </div>
      </div>

      <div className="container mx-auto px-4 -mt-10 pb-16">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
          {stats.map(({ icon: Icon, value, label }, i) => (
            <motion.div key={label} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.1 }} className="bg-white rounded-2xl border border-border/60 p-6 text-center shadow-sm">
              <Icon className="w-8 h-8 text-primary-600 mx-auto mb-3" />
              <div className="text-2xl font-extrabold text-text-primary">{value}</div>
              <p className="text-sm text-text-muted mt-1">{label}</p>
            </motion.div>
          ))}
        </div>

        <div className="max-w-3xl mx-auto space-y-6 text-text-secondary leading-relaxed">
          <h2 className="text-2xl font-bold text-text-primary">Our Story</h2>
          <p>Xelnova was founded with a simple vision: to create a marketplace where quality meets affordability. We connect small businesses, artisans, and leading brands with customers across India, ensuring everyone gets access to the best products at fair prices.</p>
          <h2 className="text-2xl font-bold text-text-primary">Our Mission</h2>
          <p>We are committed to empowering sellers with powerful tools and giving buyers a seamless, trustworthy shopping experience. From electronics to fashion, home to beauty — we curate the best so you don&apos;t have to search elsewhere.</p>
          <h2 className="text-2xl font-bold text-text-primary">Why Xelnova?</h2>
          <ul className="space-y-2 list-disc pl-5">
            <li>Curated selection of quality products from verified sellers</li>
            <li>Secure payments with buyer protection</li>
            <li>Fast delivery across 19,000+ pin codes in India</li>
            <li>Hassle-free returns and dedicated support</li>
          </ul>
        </div>
      </div>
    </div>
  );
}
