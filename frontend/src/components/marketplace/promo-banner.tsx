"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";

interface Banner {
  id: number;
  image: string;
  href: string;
  alt: string;
}

interface PromoBannerProps {
  banners: Banner[];
}

export function PromoBanner({ banners }: PromoBannerProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      {banners.map((banner, i) => (
        <motion.div
          key={banner.id}
          initial={{ opacity: 0, scale: 0.95 }}
          whileInView={{ opacity: 1, scale: 1 }}
          viewport={{ once: true }}
          transition={{ duration: 0.4, delay: i * 0.1 }}
        >
          <Link
            href={banner.href}
            className="block relative h-44 md:h-52 rounded-2xl overflow-hidden group shadow-sm hover:shadow-xl hover:shadow-black/[0.06] transition-all duration-300"
          >
            <Image
              src={banner.image}
              alt={banner.alt}
              fill
              className="object-cover group-hover:scale-105 transition-transform duration-500"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          </Link>
        </motion.div>
      ))}
    </div>
  );
}
