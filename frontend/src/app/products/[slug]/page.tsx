"use client";

import { useState } from "react";
import Image from "next/image";
import { motion } from "framer-motion";
import { Heart, ShoppingCart, Truck, RotateCcw, ShieldCheck, Star, Minus, Plus, ChevronRight, Share2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { ProductCard } from "@/components/marketplace/product-card";
import { cn, formatPrice } from "@/lib/utils";
import { MOCK_PRODUCTS } from "@/lib/constants";
import { estimateDelivery } from "@/lib/api";

const product = MOCK_PRODUCTS[0];
const images = [
  product.image,
  "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&h=600&fit=crop",
  "https://images.unsplash.com/photo-1678685888221-cda773a3dcdb?w=600&h=600&fit=crop",
  "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&h=600&fit=crop",
];

export default function ProductDetailPage() {
  const [selectedImage, setSelectedImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState<"description" | "reviews">("description");
  const [pincode, setPincode] = useState("");
  const [deliveryMessage, setDeliveryMessage] = useState<string | null>(null);
  const [deliveryLoading, setDeliveryLoading] = useState(false);
  const [deliveryError, setDeliveryError] = useState<string | null>(null);

  const handleCheckDelivery = async () => {
    const pin = pincode.trim();
    if (!pin || pin.length < 6) {
      setDeliveryError("Enter a valid 6-digit pincode");
      return;
    }
    setDeliveryError(null);
    setDeliveryMessage(null);
    setDeliveryLoading(true);
    try {
      const res = await estimateDelivery(product.id, pin);
      setDeliveryMessage(res.message || `Delivery by ${res.delivery_date}`);
    } catch (e) {
      setDeliveryError(e instanceof Error ? e.message : "Could not fetch delivery estimate");
    } finally {
      setDeliveryLoading(false);
    }
  };

  const discount = product.originalPrice
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;

  return (
    <div className="min-h-screen bg-surface-raised">
      {/* Breadcrumb */}
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600 transition-colors">Home</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <a href="/products" className="hover:text-primary-600 transition-colors">Products</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <span className="text-text-primary font-medium">{product.brand}</span>
          </nav>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm overflow-hidden">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-0">
            {/* Image Gallery */}
            <div className="p-5 lg:p-8 border-b lg:border-b-0 lg:border-r border-border-light">
              <div className="flex flex-col-reverse sm:flex-row gap-4">
                {/* Thumbnails */}
                <div className="flex sm:flex-col gap-2 overflow-x-auto sm:overflow-visible scrollbar-hide">
                  {images.map((img, i) => (
                    <button
                      key={i}
                      onClick={() => setSelectedImage(i)}
                      className={cn(
                        "w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all",
                        selectedImage === i ? "border-primary-500 shadow-md" : "border-transparent hover:border-gray-300"
                      )}
                    >
                      <Image src={img} alt="" width={64} height={64} className="w-full h-full object-cover" />
                    </button>
                  ))}
                </div>
                {/* Main Image */}
                <motion.div
                  key={selectedImage}
                  initial={{ opacity: 0.5 }}
                  animate={{ opacity: 1 }}
                  className="flex-1 relative aspect-square rounded-2xl overflow-hidden bg-surface-raised"
                >
                  <Image
                    src={images[selectedImage]}
                    alt={product.name}
                    fill
                    className="object-cover"
                    priority
                  />
                  {discount > 0 && (
                    <Badge variant="success" className="absolute top-4 left-4 bg-primary-600 text-white shadow-md text-sm px-3 py-1">
                      {discount}% OFF
                    </Badge>
                  )}
                  <button className="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/90 flex items-center justify-center shadow hover:bg-white transition">
                    <Heart className="w-5 h-5 text-gray-400 hover:text-red-500 transition-colors" />
                  </button>
                </motion.div>
              </div>
            </div>

            {/* Product Info */}
            <div className="p-5 lg:p-8">
              <div className="mb-1">
                <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">{product.brand}</p>
                <h1 className="text-xl lg:text-2xl font-bold text-text-primary leading-tight">{product.name}</h1>
              </div>

              {/* Rating */}
              <div className="flex items-center gap-3 mt-3 mb-4">
                <div className="flex items-center gap-1 bg-primary-600 text-white text-sm font-bold px-2.5 py-1 rounded-lg">
                  {product.rating} <Star className="w-3.5 h-3.5 fill-current" />
                </div>
                <span className="text-sm text-text-muted">{product.reviewsCount?.toLocaleString()} Ratings & Reviews</span>
                <button className="ml-auto"><Share2 className="w-4 h-4 text-text-muted hover:text-text-primary transition" /></button>
              </div>

              <div className="border-t border-border-light pt-4 mb-4">
                <div className="flex items-baseline gap-3 flex-wrap">
                  <span className="text-3xl font-extrabold text-text-primary">{formatPrice(product.price)}</span>
                  {product.originalPrice && (
                    <span className="text-lg text-text-muted line-through">{formatPrice(product.originalPrice)}</span>
                  )}
                  {discount > 0 && (
                    <span className="text-sm font-bold text-primary-600">{discount}% off</span>
                  )}
                </div>
                <p className="text-xs text-text-muted mt-1">Inclusive of all taxes</p>
              </div>

              {/* Quantity */}
              <div className="flex items-center gap-4 mb-6">
                <span className="text-sm font-medium text-text-secondary">Quantity:</span>
                <div className="flex items-center border border-border rounded-xl overflow-hidden">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition-colors"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="w-12 h-10 flex items-center justify-center font-semibold text-sm border-x border-border">
                    {quantity}
                  </span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-3 mb-6">
                <Button size="lg" className="flex-1 py-3.5">
                  <ShoppingCart className="w-5 h-5" />
                  Add to Cart
                </Button>
                <Button size="lg" variant="accent" className="flex-1 py-3.5">
                  Buy Now
                </Button>
              </div>

              {/* Features */}
              <div className="grid grid-cols-3 gap-3 p-4 bg-surface-raised rounded-xl">
                {[
                  { icon: Truck, label: "Free Delivery" },
                  { icon: RotateCcw, label: "30-Day Returns" },
                  { icon: ShieldCheck, label: "Secure Payment" },
                ].map(({ icon: Icon, label }) => (
                  <div key={label} className="text-center">
                    <Icon className="w-5 h-5 text-primary-600 mx-auto mb-1" />
                    <p className="text-[11px] text-text-muted font-medium">{label}</p>
                  </div>
                ))}
              </div>

              {/* Delivery Check */}
              <div className="mt-6 p-4 border border-border rounded-xl">
                <h4 className="text-sm font-semibold text-text-primary mb-2">Check Delivery</h4>
                <div className="flex gap-2">
                  <input
                    type="text"
                    placeholder="Enter pincode"
                    maxLength={6}
                    value={pincode}
                    onChange={(e) => setPincode(e.target.value.replace(/\D/g, "").slice(0, 6))}
                    className="flex-1 rounded-lg border border-border px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                  />
                  <Button variant="outline" size="sm" onClick={handleCheckDelivery} disabled={deliveryLoading}>
                    {deliveryLoading ? "Checking..." : "Check"}
                  </Button>
                </div>
                {deliveryMessage && <p className="text-sm text-primary-600 font-medium mt-2">{deliveryMessage}</p>}
                {deliveryError && <p className="text-sm text-red-600 mt-2">{deliveryError}</p>}
              </div>
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm mt-4 overflow-hidden">
          <div className="flex border-b border-border-light">
            {(["description", "reviews"] as const).map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={cn(
                  "px-6 py-4 text-sm font-semibold capitalize transition-colors relative",
                  activeTab === tab ? "text-primary-700" : "text-text-muted hover:text-text-primary"
                )}
              >
                {tab === "reviews" ? "Ratings & Reviews" : "Description"}
                {activeTab === tab && (
                  <motion.div layoutId="tab-underline" className="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-600" />
                )}
              </button>
            ))}
          </div>
          <div className="p-6">
            {activeTab === "description" ? (
              <div className="prose prose-sm max-w-none text-text-secondary leading-relaxed">
                <p>Experience the next level of innovation with the {product.name}. Packed with cutting-edge features and a sleek design, it&apos;s built for those who demand the best.</p>
                <h3 className="text-text-primary font-semibold mt-4 mb-2">Key Features</h3>
                <ul className="space-y-1">
                  <li>Premium build quality with modern design</li>
                  <li>High-performance processor for seamless multitasking</li>
                  <li>Advanced camera system for stunning photography</li>
                  <li>Long-lasting battery for all-day use</li>
                  <li>1 Year manufacturer warranty</li>
                </ul>
              </div>
            ) : (
              <div>
                <div className="flex items-center gap-4 mb-6">
                  <div className="text-center">
                    <div className="text-4xl font-extrabold text-text-primary">{product.rating}</div>
                    <div className="flex justify-center mt-1">
                      {Array.from({ length: 5 }).map((_, i) => (
                        <Star key={i} className={cn("w-4 h-4", i < Math.round(product.rating || 0) ? "text-accent-400 fill-accent-400" : "text-gray-200")} />
                      ))}
                    </div>
                    <p className="text-xs text-text-muted mt-1">{product.reviewsCount?.toLocaleString()} reviews</p>
                  </div>
                </div>
                {[5, 4, 3].map((stars) => (
                  <div key={stars} className="flex items-center gap-3 mb-2">
                    <span className="text-sm text-text-muted w-6">{stars}★</span>
                    <div className="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-primary-500 rounded-full"
                        style={{ width: `${stars === 5 ? 65 : stars === 4 ? 22 : 8}%` }}
                      />
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Similar Products */}
        <div className="mt-8 mb-8">
          <h2 className="text-xl font-bold text-text-primary mb-4">Similar Products</h2>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
            {MOCK_PRODUCTS.slice(1, 7).map((p, i) => (
              <ProductCard key={p.id} product={p} index={i} />
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
