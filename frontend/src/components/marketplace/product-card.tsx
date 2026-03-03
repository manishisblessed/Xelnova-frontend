"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { motion } from "framer-motion";
import { Heart, Star, Truck } from "lucide-react";
import { cn, formatPrice, calculateDiscount } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";

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

interface ProductCardProps {
  product: Product;
  index?: number;
}

export function ProductCard({ product, index = 0 }: ProductCardProps) {
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  const discount = product.originalPrice
    ? calculateDiscount(product.price, product.originalPrice)
    : 0;

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{
        duration: 0.4,
        delay: index * 0.05,
        ease: [0.16, 1, 0.3, 1],
      }}
      className="group h-full"
    >
      <div className="bg-white rounded-2xl border border-border/60 overflow-hidden h-full flex flex-col transition-all duration-300 hover:shadow-xl hover:shadow-black/[0.06] hover:border-border hover:-translate-y-1">
        {/* Image */}
        <div className="relative aspect-[4/5] overflow-hidden bg-surface-raised">
          <Link href={`/products/${product.slug}`} className="block relative w-full h-full">
            {!imageLoaded && <div className="absolute inset-0 animate-shimmer" />}
            <Image
              src={product.image}
              alt={product.name}
              fill
              className={cn(
                "object-cover object-center transition-transform duration-500 group-hover:scale-105",
                imageLoaded ? "opacity-100" : "opacity-0"
              )}
              onLoad={() => setImageLoaded(true)}
              sizes="(max-width: 640px) 50vw, (max-width: 1024px) 25vw, 16vw"
            />
          </Link>

          {/* Wishlist */}
          <motion.button
            whileTap={{ scale: 0.85 }}
            onClick={() => setIsWishlisted(!isWishlisted)}
            className={cn(
              "absolute top-3 right-3 w-9 h-9 rounded-full flex items-center justify-center z-10 transition-all duration-200 shadow-sm",
              isWishlisted
                ? "bg-red-50 text-red-500"
                : "bg-white/90 text-gray-400 hover:text-red-500 hover:bg-white opacity-0 group-hover:opacity-100"
            )}
          >
            <Heart
              className="w-4.5 h-4.5"
              fill={isWishlisted ? "currentColor" : "none"}
            />
          </motion.button>

          {/* Discount Badge */}
          {discount > 0 && (
            <div className="absolute top-3 left-3 z-10">
              <Badge variant="success" className="bg-primary-600 text-white shadow-sm">
                {discount}% OFF
              </Badge>
            </div>
          )}
        </div>

        {/* Content */}
        <div className="p-4 flex-grow flex flex-col gap-1.5">
          {/* Brand */}
          {product.brand && (
            <p className="text-[11px] font-semibold text-text-muted uppercase tracking-wider">
              {product.brand}
            </p>
          )}

          {/* Title */}
          <h3 className="text-sm font-medium text-text-primary line-clamp-2 leading-snug group-hover:text-primary-700 transition-colors">
            <Link href={`/products/${product.slug}`}>{product.name}</Link>
          </h3>

          {/* Rating */}
          {product.rating && (
            <div className="flex items-center gap-2">
              <div className="flex items-center gap-1 bg-primary-600 text-white text-[11px] font-bold px-2 py-0.5 rounded-md">
                <span>{product.rating}</span>
                <Star className="w-3 h-3 fill-current" />
              </div>
              {product.reviewsCount && (
                <span className="text-xs text-text-muted">
                  ({product.reviewsCount.toLocaleString()})
                </span>
              )}
            </div>
          )}

          {/* Price */}
          <div className="mt-auto pt-2">
            <div className="flex items-baseline gap-2 flex-wrap">
              <span className="text-lg font-bold text-text-primary">
                {formatPrice(product.price)}
              </span>
              {product.originalPrice && (
                <span className="text-sm text-text-muted line-through">
                  {formatPrice(product.originalPrice)}
                </span>
              )}
            </div>

            {product.freeDelivery && (
              <p className="flex items-center gap-1 text-xs text-primary-600 font-medium mt-1.5">
                <Truck className="w-3.5 h-3.5" />
                Free delivery
              </p>
            )}
          </div>
        </div>
      </div>
    </motion.div>
  );
}
