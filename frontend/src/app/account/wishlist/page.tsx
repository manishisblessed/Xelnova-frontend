"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Heart, ChevronRight, ShoppingBag } from "lucide-react";
import Link from "next/link";
import { ProductCard } from "@/components/marketplace/product-card";
import { Button } from "@/components/ui/button";
import { MOCK_PRODUCTS } from "@/lib/constants";

export default function WishlistPage() {
  const [wishlist, setWishlist] = useState(MOCK_PRODUCTS.slice(0, 6));

  if (wishlist.length === 0) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center px-4">
        <div className="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center mb-4">
          <Heart className="w-10 h-10 text-red-300" />
        </div>
        <h2 className="text-xl font-bold text-text-primary mb-2">Your wishlist is empty</h2>
        <p className="text-text-muted mb-6">Save items you love to buy them later.</p>
        <Link href="/products"><Button>Explore Products</Button></Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600">Home</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <span className="text-text-primary font-medium">My Wishlist</span>
          </nav>
        </div>
      </div>
      <div className="container mx-auto px-4 py-6">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold text-text-primary">My Wishlist ({wishlist.length})</h1>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
          <AnimatePresence>
            {wishlist.map((product, i) => (
              <ProductCard key={product.id} product={product} index={i} />
            ))}
          </AnimatePresence>
        </div>
      </div>
    </div>
  );
}
