"use client";

import { useState, useEffect, useCallback } from "react";
import Image from "next/image";
import { motion, AnimatePresence } from "framer-motion";
import { ChevronLeft, ChevronRight, ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";

interface Slide {
  id: number;
  image: string;
  title: string;
  subtitle: string;
  badge?: string;
  cta: string;
  href: string;
  gradient: string;
  accent: string;
}

const slides: Slide[] = [
  {
    id: 1,
    image: "https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=1400&h=500&fit=crop",
    title: "Electronics Mega Sale",
    subtitle: "Up to 70% off on smartphones, laptops & accessories",
    badge: "Limited Time",
    cta: "Shop Electronics",
    href: "/products?category=electronics",
    gradient: "from-black/70 via-black/40 to-transparent",
    accent: "bg-primary-500",
  },
  {
    id: 2,
    image: "https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1400&h=500&fit=crop",
    title: "New Season Fashion",
    subtitle: "Trending styles at unbeatable prices — refresh your wardrobe",
    badge: "Trending Now",
    cta: "Explore Fashion",
    href: "/products?category=fashion",
    gradient: "from-black/70 via-black/40 to-transparent",
    accent: "bg-accent-500",
  },
  {
    id: 3,
    image: "https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=1400&h=500&fit=crop",
    title: "Home & Living Fest",
    subtitle: "Transform your space with curated furniture & decor",
    badge: "Best Sellers",
    cta: "Discover More",
    href: "/products?category=home-furniture",
    gradient: "from-black/70 via-black/40 to-transparent",
    accent: "bg-primary-500",
  },
];

const slideVariants = {
  enter: (direction: number) => ({
    x: direction > 0 ? 200 : -200,
    opacity: 0,
  }),
  center: {
    x: 0,
    opacity: 1,
  },
  exit: (direction: number) => ({
    x: direction < 0 ? 200 : -200,
    opacity: 0,
  }),
};

export function HeroCarousel() {
  const [[page, direction], setPage] = useState([0, 0]);
  const activeIndex = ((page % slides.length) + slides.length) % slides.length;

  const paginate = useCallback(
    (newDirection: number) => {
      setPage([page + newDirection, newDirection]);
    },
    [page]
  );

  useEffect(() => {
    const timer = setInterval(() => paginate(1), 6000);
    return () => clearInterval(timer);
  }, [paginate]);

  const slide = slides[activeIndex];

  return (
    <div className="relative overflow-hidden rounded-2xl lg:rounded-3xl bg-surface-dark group shadow-lg shadow-black/10">
      <div className="relative h-52 sm:h-64 md:h-80 lg:h-[440px]">
        <AnimatePresence initial={false} custom={direction} mode="popLayout">
          <motion.div
            key={page}
            custom={direction}
            variants={slideVariants}
            initial="enter"
            animate="center"
            exit="exit"
            transition={{
              x: { type: "spring", stiffness: 300, damping: 30 },
              opacity: { duration: 0.25 },
            }}
            className="absolute inset-0"
          >
            <Image
              src={slide.image}
              alt={slide.title}
              fill
              priority
              className="object-cover"
              sizes="(max-width: 768px) 100vw, 1400px"
            />
            <div className={cn("absolute inset-0 bg-gradient-to-r", slide.gradient)} />

            {/* Content */}
            <div className="absolute inset-0 flex items-center">
              <div className="container mx-auto px-6 md:px-12 lg:px-16">
                <motion.div
                  initial={{ opacity: 0, y: 24 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.15, duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
                  className="max-w-lg"
                >
                  {slide.badge && (
                    <span className={cn(
                      "inline-block text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-4",
                      slide.accent
                    )}>
                      {slide.badge}
                    </span>
                  )}
                  <h2 className="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-white mb-3 leading-[1.1]">
                    {slide.title}
                  </h2>
                  <p className="text-sm sm:text-base md:text-lg text-white/70 mb-6 leading-relaxed max-w-md">
                    {slide.subtitle}
                  </p>
                  <a
                    href={slide.href}
                    className="inline-flex items-center gap-2.5 bg-white text-text-primary px-6 py-3 rounded-xl font-semibold text-sm hover:bg-primary-50 hover:text-primary-700 transition-all duration-200 shadow-xl shadow-black/15 active:scale-[0.97] group/btn"
                  >
                    {slide.cta}
                    <ArrowRight className="w-4 h-4 group-hover/btn:translate-x-0.5 transition-transform" />
                  </a>
                </motion.div>
              </div>
            </div>
          </motion.div>
        </AnimatePresence>

        {/* Nav Buttons */}
        <button
          onClick={() => paginate(-1)}
          className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-300 active:scale-90"
        >
          <ChevronLeft className="w-5 h-5" />
        </button>
        <button
          onClick={() => paginate(1)}
          className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-300 active:scale-90"
        >
          <ChevronRight className="w-5 h-5" />
        </button>

        {/* Indicators */}
        <div className="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2">
          {slides.map((_, i) => (
            <button
              key={i}
              onClick={() => setPage([i, i > activeIndex ? 1 : -1])}
              className="relative h-1.5 rounded-full overflow-hidden transition-all duration-500"
              style={{ width: i === activeIndex ? 32 : 8 }}
            >
              <div className={cn(
                "absolute inset-0 rounded-full transition-colors duration-300",
                i === activeIndex ? "bg-white" : "bg-white/40"
              )} />
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
