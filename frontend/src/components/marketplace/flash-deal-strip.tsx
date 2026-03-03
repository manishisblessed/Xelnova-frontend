"use client";

import { motion } from "framer-motion";
import { Zap, ChevronRight } from "lucide-react";
import Link from "next/link";
import { ProductCard } from "./product-card";

interface Product {
  id: number;
  name: string;
  slug: string;
  image: string;
  price: number;
  originalPrice?: number;
  rating?: number;
  reviewsCount?: number;
  brand?: string;
  freeDelivery?: boolean;
}

interface FlashDealStripProps {
  title: string;
  products: Product[];
  href?: string;
  bgImage?: string;
}

export function FlashDealStrip({
  title,
  products,
  href = "/products",
}: FlashDealStripProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 30 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
      className="bg-white rounded-2xl border border-border/60 overflow-hidden shadow-sm"
    >
      <div className="flex flex-col md:flex-row">
        {/* Deal Info Panel */}
        <div className="md:w-56 lg:w-64 bg-gradient-to-br from-primary-700 via-primary-600 to-primary-800 p-6 flex flex-col justify-center items-center text-center relative overflow-hidden">
          <div className="absolute inset-0 opacity-10">
            <div className="absolute top-4 left-4 w-32 h-32 rounded-full bg-white/20 blur-2xl" />
            <div className="absolute bottom-4 right-4 w-24 h-24 rounded-full bg-accent-400/30 blur-xl" />
          </div>
          <motion.div
            initial={{ scale: 0 }}
            whileInView={{ scale: 1 }}
            viewport={{ once: true }}
            transition={{ type: "spring", stiffness: 400, delay: 0.2 }}
            className="relative"
          >
            <div className="w-12 h-12 rounded-2xl bg-accent-400 flex items-center justify-center mb-4 shadow-lg shadow-accent-500/30 mx-auto">
              <Zap className="w-6 h-6 text-white fill-white" />
            </div>
            <h2 className="text-2xl lg:text-3xl font-bold text-white mb-2 leading-tight">
              {title}
            </h2>
            <p className="text-sm text-white/60 mb-5">
              Limited time offers
            </p>
            <Link
              href={href}
              className="inline-flex items-center gap-1.5 bg-white text-primary-700 px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-primary-50 transition-all shadow-lg shadow-black/10 active:scale-95"
            >
              View All
              <ChevronRight className="w-4 h-4" />
            </Link>
          </motion.div>
        </div>

        {/* Products Scroll */}
        <div className="flex-1 overflow-x-auto scrollbar-hide">
          <div className="flex gap-4 p-4 min-w-max">
            {products.map((product, i) => (
              <div key={product.id} className="w-44 lg:w-48 flex-shrink-0">
                <ProductCard product={product} index={i} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </motion.div>
  );
}
