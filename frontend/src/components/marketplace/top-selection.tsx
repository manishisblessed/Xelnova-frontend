"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight } from "lucide-react";

interface SelectionItem {
  id: number;
  title: string;
  discount: string;
  image: string;
  href: string;
}

interface TopSelectionProps {
  items: SelectionItem[];
}

export function TopSelection({ items }: TopSelectionProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
      {items.map((item, i) => (
        <motion.div
          key={item.id}
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.4, delay: i * 0.1 }}
        >
          <Link
            href={item.href}
            className="group flex items-center gap-5 bg-white border border-border/60 rounded-2xl p-5 hover:shadow-lg hover:shadow-black/[0.05] hover:-translate-y-0.5 transition-all duration-300"
          >
            <div className="w-24 h-24 flex-shrink-0 rounded-xl bg-surface-raised overflow-hidden flex items-center justify-center">
              <Image
                src={item.image}
                alt={item.title}
                width={96}
                height={96}
                className="w-20 h-20 object-contain group-hover:scale-110 transition-transform duration-300"
              />
            </div>
            <div className="flex-1">
              <h3 className="font-semibold text-text-primary group-hover:text-primary-700 transition-colors">
                {item.title}
              </h3>
              <p className="text-sm text-primary-600 font-medium mt-0.5">
                {item.discount}
              </p>
              <span className="inline-flex items-center gap-1 text-xs font-semibold text-text-muted group-hover:text-primary-600 mt-3 transition-colors">
                Shop Now
                <ArrowRight className="w-3 h-3 group-hover:translate-x-0.5 transition-transform" />
              </span>
            </div>
          </Link>
        </motion.div>
      ))}
    </div>
  );
}
