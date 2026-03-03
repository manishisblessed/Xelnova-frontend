"use client";

import { useState, useEffect, useCallback, Suspense } from "react";
import { useSearchParams } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import { SlidersHorizontal, ChevronDown, X, Grid3X3, LayoutList, Star } from "lucide-react";
import { ProductCard } from "@/components/marketplace/product-card";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { getProducts } from "@/lib/api";
import type { Product } from "@/lib/api";

const sortOptions = [
  { label: "Relevance", value: "relevance" },
  { label: "Price: Low to High", value: "price_low" },
  { label: "Price: High to Low", value: "price_high" },
  { label: "Newest First", value: "latest" },
];

const priceRanges = [
  { label: "Under ₹1,000", min: 0, max: 1000 },
  { label: "₹1,000 - ₹5,000", min: 1000, max: 5000 },
  { label: "₹5,000 - ₹20,000", min: 5000, max: 20000 },
  { label: "₹20,000 - ₹50,000", min: 20000, max: 50000 },
  { label: "Over ₹50,000", min: 50000, max: Infinity },
];

const brands = ["Samsung", "Apple", "Sony", "Nike", "Adidas", "Canon", "JBL", "Dyson", "Levi's", "Ray-Ban"];

function ProductsPageSkeleton() {
  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <div className="h-4 bg-gray-100 rounded w-48 animate-pulse" />
        </div>
      </div>
      <div className="container mx-auto px-4 py-6">
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="rounded-2xl border border-border/60 overflow-hidden bg-white animate-pulse">
              <div className="aspect-[4/5] bg-gray-100" />
              <div className="p-4 space-y-2">
                <div className="h-3 bg-gray-100 rounded w-3/4" />
                <div className="h-4 bg-gray-100 rounded w-1/2" />
                <div className="h-5 bg-gray-100 rounded w-1/3" />
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function ProductsContent() {
  const searchParams = useSearchParams();
  const urlCategory = searchParams.get("category") ?? undefined;
  const urlSearch = searchParams.get("search") ?? undefined;

  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number; has_more: boolean } | null>(null);
  const [sortBy, setSortBy] = useState("latest");
  const [showFilters, setShowFilters] = useState(false);
  const [gridView, setGridView] = useState(true);
  const [selectedBrands, setSelectedBrands] = useState<string[]>([]);
  const [selectedPriceRange, setSelectedPriceRange] = useState<{ min: number; max: number } | null>(null);
  const [selectedRating, setSelectedRating] = useState<number | null>(null);

  const loadProducts = useCallback(
    async (page = 1, append = false) => {
      setLoading(true);
      try {
        const sort = sortBy === "relevance" ? "latest" : sortBy;
        const res = await getProducts({
          page,
          per_page: 24,
          category: urlCategory,
          search: urlSearch,
          brand: selectedBrands.length > 0 ? selectedBrands : undefined,
          min_price: selectedPriceRange?.min,
          max_price: selectedPriceRange?.max === Infinity ? undefined : selectedPriceRange?.max,
          sort: sort as "latest" | "price_low" | "price_high",
        });
        setProducts((prev) => (append ? [...prev, ...res.products] : res.products));
        setMeta(res.meta);
      } catch {
        if (!append) setProducts([]);
        setMeta(null);
      } finally {
        setLoading(false);
      }
    },
    [urlCategory, urlSearch, sortBy, selectedBrands, selectedPriceRange]
  );

  useEffect(() => {
    loadProducts(1, false);
  }, [loadProducts]);

  const toggleBrand = (brand: string) => {
    setSelectedBrands((prev) =>
      prev.includes(brand) ? prev.filter((b) => b !== brand) : [...prev, brand]
    );
  };

  const loadMore = () => {
    if (meta && meta.has_more && !loading) {
      loadProducts(meta.current_page + 1, true);
    }
  };

  return (
    <div className="min-h-screen bg-surface-raised">
      {/* Breadcrumb */}
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600 transition-colors">Home</a>
            <span className="mx-2">/</span>
            <span className="text-text-primary font-medium">All Products</span>
          </nav>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <div className="flex gap-6">
          {/* Filters Sidebar — Desktop */}
          <aside className="hidden lg:block w-64 flex-shrink-0">
            <div className="bg-white rounded-2xl border border-border/60 p-5 sticky top-44 shadow-sm">
              <h3 className="font-bold text-text-primary mb-4">Filters</h3>

              {/* Price */}
              <div className="mb-6">
                <h4 className="text-sm font-semibold text-text-primary mb-3">Price</h4>
                <div className="space-y-2">
                  {priceRanges.map((range) => (
                    <label key={range.label} className="flex items-center gap-2 text-sm text-text-secondary hover:text-text-primary cursor-pointer">
                      <input
                        type="checkbox"
                        checked={selectedPriceRange?.min === range.min && selectedPriceRange?.max === range.max}
                        onChange={() => setSelectedPriceRange((prev) => (prev?.min === range.min ? null : { min: range.min, max: range.max }))}
                        className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                      />
                      {range.label}
                    </label>
                  ))}
                </div>
              </div>

              {/* Brand */}
              <div className="mb-6">
                <h4 className="text-sm font-semibold text-text-primary mb-3">Brand</h4>
                <div className="space-y-2">
                  {brands.map((brand) => (
                    <label key={brand} className="flex items-center gap-2 text-sm text-text-secondary hover:text-text-primary cursor-pointer">
                      <input
                        type="checkbox"
                        checked={selectedBrands.includes(brand)}
                        onChange={() => toggleBrand(brand)}
                        className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                      />
                      {brand}
                    </label>
                  ))}
                </div>
              </div>

              {/* Rating */}
              <div>
                <h4 className="text-sm font-semibold text-text-primary mb-3">Rating</h4>
                <div className="space-y-2">
                  {[4, 3, 2].map((rating) => (
                    <button
                      key={rating}
                      onClick={() => setSelectedRating(selectedRating === rating ? null : rating)}
                      className={cn(
                        "flex items-center gap-2 text-sm w-full px-2 py-1.5 rounded-lg transition-colors",
                        selectedRating === rating
                          ? "bg-primary-50 text-primary-700"
                          : "text-text-secondary hover:bg-gray-50"
                      )}
                    >
                      <div className="flex items-center gap-0.5">
                        {Array.from({ length: 5 }).map((_, i) => (
                          <Star key={i} className={cn("w-3.5 h-3.5", i < rating ? "text-accent-400 fill-accent-400" : "text-gray-300")} />
                        ))}
                      </div>
                      <span>& Up</span>
                    </button>
                  ))}
                </div>
              </div>
            </div>
          </aside>

          {/* Main Content */}
          <div className="flex-1 min-w-0">
            {/* Toolbar */}
            <div className="bg-white rounded-2xl border border-border/60 p-4 mb-4 shadow-sm flex items-center justify-between gap-4">
              <div className="flex items-center gap-3">
                <button
                  onClick={() => setShowFilters(!showFilters)}
                  className="lg:hidden flex items-center gap-2 px-3 py-2 rounded-xl border border-border text-sm font-medium hover:bg-gray-50 transition-colors"
                >
                  <SlidersHorizontal className="w-4 h-4" />
                  Filters
                </button>
                <p className="text-sm text-text-muted hidden sm:block">
                  {loading && products.length === 0
                    ? "Loading..."
                    : `Showing ${products.length}${meta ? ` of ${meta.total}` : ""} products`}
                </p>
              </div>

              <div className="flex items-center gap-3">
                <div className="relative">
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value)}
                    className="appearance-none bg-gray-50 border border-border rounded-xl py-2 pl-3 pr-8 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer"
                  >
                    {sortOptions.map((opt) => (
                      <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
                </div>

                <div className="hidden sm:flex border border-border rounded-xl overflow-hidden">
                  <button
                    onClick={() => setGridView(true)}
                    className={cn("p-2 transition-colors", gridView ? "bg-primary-50 text-primary-700" : "hover:bg-gray-50 text-text-muted")}
                  >
                    <Grid3X3 className="w-4 h-4" />
                  </button>
                  <button
                    onClick={() => setGridView(false)}
                    className={cn("p-2 transition-colors", !gridView ? "bg-primary-50 text-primary-700" : "hover:bg-gray-50 text-text-muted")}
                  >
                    <LayoutList className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            {/* Active Filters */}
            {selectedBrands.length > 0 && (
              <div className="flex flex-wrap items-center gap-2 mb-4">
                {selectedBrands.map((brand) => (
                  <span key={brand} className="inline-flex items-center gap-1 bg-primary-50 text-primary-700 text-xs font-medium px-3 py-1.5 rounded-full">
                    {brand}
                    <button onClick={() => toggleBrand(brand)}><X className="w-3 h-3" /></button>
                  </span>
                ))}
                <button onClick={() => setSelectedBrands([])} className="text-xs text-text-muted hover:text-red-500 transition-colors">
                  Clear all
                </button>
              </div>
            )}

            {/* Product Grid */}
            {loading && products.length === 0 ? (
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                {Array.from({ length: 8 }).map((_, i) => (
                  <div key={i} className="rounded-2xl border border-border/60 overflow-hidden bg-white animate-pulse">
                    <div className="aspect-[4/5] bg-gray-100" />
                    <div className="p-4 space-y-2">
                      <div className="h-3 bg-gray-100 rounded w-3/4" />
                      <div className="h-4 bg-gray-100 rounded w-1/2" />
                      <div className="h-5 bg-gray-100 rounded w-1/3" />
                    </div>
                  </div>
                ))}
              </div>
            ) : (
            <div className={cn(
              "grid gap-3 md:gap-4",
              gridView
                ? "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4"
                : "grid-cols-1"
            )}>
              {products.map((product, i) => (
                <ProductCard key={`${product.id}-${product.slug}-${i}`} product={product} index={i} />
              ))}
            </div>
            )}

            {/* Load More */}
            {meta?.has_more && (
            <div className="text-center mt-8">
              <Button variant="outline" size="lg" onClick={loadMore} disabled={loading}>
                {loading ? "Loading..." : "Load More Products"}
              </Button>
            </div>
            )}
          </div>
        </div>
      </div>

      {/* Mobile Filter Drawer */}
      <AnimatePresence>
        {showFilters && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setShowFilters(false)}
              className="fixed inset-0 bg-black/40 z-50 lg:hidden"
            />
            <motion.div
              initial={{ y: "100%" }}
              animate={{ y: 0 }}
              exit={{ y: "100%" }}
              transition={{ type: "spring", damping: 30, stiffness: 300 }}
              className="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl z-50 lg:hidden max-h-[80vh] overflow-y-auto"
            >
              <div className="p-5">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-bold text-lg">Filters</h3>
                  <button onClick={() => setShowFilters(false)}>
                    <X className="w-5 h-5 text-text-muted" />
                  </button>
                </div>
                <div className="mb-6">
                  <h4 className="text-sm font-semibold text-text-primary mb-3">Brand</h4>
                  <div className="flex flex-wrap gap-2">
                    {brands.map((brand) => (
                      <button
                        key={brand}
                        onClick={() => toggleBrand(brand)}
                        className={cn(
                          "px-3 py-1.5 rounded-full text-sm font-medium border transition-colors",
                          selectedBrands.includes(brand)
                            ? "bg-primary-50 border-primary-300 text-primary-700"
                            : "border-border text-text-secondary hover:border-gray-300"
                        )}
                      >
                        {brand}
                      </button>
                    ))}
                  </div>
                </div>
                <Button onClick={() => setShowFilters(false)} className="w-full">
                  Apply Filters
                </Button>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>
    </div>
  );
}

export default function ProductsPage() {
  return (
    <Suspense fallback={<ProductsPageSkeleton />}>
      <ProductsContent />
    </Suspense>
  );
}
