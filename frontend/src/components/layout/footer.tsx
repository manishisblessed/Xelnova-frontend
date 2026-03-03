"use client";

import Link from "next/link";
import Image from "next/image";
import { motion } from "framer-motion";
import { Facebook, Twitter, Youtube, Instagram, Send } from "lucide-react";
import { FOOTER_LINKS, SITE_NAME } from "@/lib/constants";
import { Button } from "@/components/ui/button";

const containerVariants = {
  hidden: {},
  visible: { transition: { staggerChildren: 0.08 } },
};

const itemVariants = {
  hidden: { opacity: 0, y: 16 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.5, ease: [0.16, 1, 0.3, 1] as [number, number, number, number] },
  },
};

export function Footer() {
  return (
    <footer className="bg-surface-dark text-white/80">
      {/* Newsletter */}
      <div className="border-b border-white/10">
        <div className="container mx-auto px-4 py-10">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
              <h3 className="text-xl font-bold text-white mb-1">
                Stay in the loop
              </h3>
              <p className="text-sm text-white/50">
                Subscribe for exclusive offers, new arrivals & insider-only discounts.
              </p>
            </div>
            <div className="flex w-full md:w-auto gap-2">
              <div className="relative flex-1 md:w-80">
                <input
                  type="email"
                  placeholder="Enter your email"
                  className="w-full bg-white/10 border border-white/10 rounded-xl py-3 pl-4 pr-4 text-sm text-white placeholder:text-white/40 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 transition-all"
                />
              </div>
              <Button variant="primary" size="lg" className="rounded-xl whitespace-nowrap">
                <Send className="w-4 h-4" />
                Subscribe
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Links */}
      <motion.div
        variants={containerVariants}
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
        className="container mx-auto px-4 py-12"
      >
        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
          {/* Brand */}
          <motion.div variants={itemVariants} className="col-span-2 md:col-span-1 lg:col-span-1">
            <Link href="/" className="flex items-center gap-2 mb-4">
              <Image
                src="/xelnova-logo.png"
                alt={SITE_NAME}
                width={120}
                height={34}
                className="h-8 w-auto object-contain opacity-90 hover:opacity-100 transition-opacity"
              />
            </Link>
            <p className="text-sm text-white/40 leading-relaxed mb-5 max-w-xs">
              Your one-stop marketplace for electronics, fashion, home & more. Quality products at great prices.
            </p>
            {/* Social */}
            <div className="flex gap-3">
              {[
                { icon: Facebook, href: "#" },
                { icon: Twitter, href: "#" },
                { icon: Instagram, href: "#" },
                { icon: Youtube, href: "#" },
              ].map(({ icon: Icon, href }, i) => (
                <a
                  key={i}
                  href={href}
                  className="w-9 h-9 rounded-xl bg-white/5 hover:bg-primary-600 flex items-center justify-center text-white/50 hover:text-white transition-all duration-200"
                >
                  <Icon className="w-4 h-4" />
                </a>
              ))}
            </div>
          </motion.div>

          {/* About Links */}
          <motion.div variants={itemVariants}>
            <h4 className="text-xs font-semibold text-white/30 uppercase tracking-widest mb-4">
              About
            </h4>
            <ul className="space-y-2.5">
              {FOOTER_LINKS.about.map((link) => (
                <li key={link.name}>
                  <Link
                    href={link.href}
                    className="text-sm text-white/50 hover:text-white transition-colors duration-200"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Help Links */}
          <motion.div variants={itemVariants}>
            <h4 className="text-xs font-semibold text-white/30 uppercase tracking-widest mb-4">
              Help
            </h4>
            <ul className="space-y-2.5">
              {FOOTER_LINKS.help.map((link) => (
                <li key={link.name}>
                  <Link
                    href={link.href}
                    className="text-sm text-white/50 hover:text-white transition-colors duration-200"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Tools Links */}
          <motion.div variants={itemVariants}>
            <h4 className="text-xs font-semibold text-white/30 uppercase tracking-widest mb-4">
              Tools
            </h4>
            <ul className="space-y-2.5">
              {FOOTER_LINKS.tools.map((link) => (
                <li key={link.name}>
                  <Link
                    href={link.href}
                    className="text-sm text-white/50 hover:text-white transition-colors duration-200"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Policy Links */}
          <motion.div variants={itemVariants}>
            <h4 className="text-xs font-semibold text-white/30 uppercase tracking-widest mb-4">
              Consumer Policy
            </h4>
            <ul className="space-y-2.5">
              {FOOTER_LINKS.policy.map((link) => (
                <li key={link.name}>
                  <Link
                    href={link.href}
                    className="text-sm text-white/50 hover:text-white transition-colors duration-200"
                  >
                    {link.name}
                  </Link>
                </li>
              ))}
            </ul>
          </motion.div>

          {/* Office */}
          <motion.div variants={itemVariants}>
            <h4 className="text-xs font-semibold text-white/30 uppercase tracking-widest mb-4">
              Registered Office
            </h4>
            <p className="text-xs text-white/40 leading-relaxed">
              XELNOVA PRIVATE LIMITED,
              <br />
              122/1, New Line, Maha Laxmi
              <br />
              Dharam Kanta, Bamnoli,
              <br />
              Najafgarh, New Delhi
              <br />
              South West Delhi - 110077
            </p>
          </motion.div>
        </div>
      </motion.div>

      {/* Bottom Bar */}
      <div className="border-t border-white/5">
        <div className="container mx-auto px-4 py-5 flex flex-col md:flex-row justify-between items-center gap-3">
          <p className="text-xs text-white/30">
            &copy; {new Date().getFullYear()} {SITE_NAME}. All rights reserved.
          </p>
          <div className="flex items-center gap-3">
            {["Visa", "Mastercard", "UPI", "Rupay", "Net Banking"].map((method) => (
              <span
                key={method}
                className="text-[10px] text-white/30 bg-white/5 px-2.5 py-1 rounded-md font-medium"
              >
                {method}
              </span>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
