"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { ChevronDown, HelpCircle } from "lucide-react";
import { cn } from "@/lib/utils";

const faqs = [
  { q: "How do I place an order?", a: "Browse products, add them to your cart, and proceed to checkout. You can pay online via UPI, cards, or net banking, or choose Cash on Delivery." },
  { q: "What payment methods do you accept?", a: "We accept UPI, credit/debit cards (Visa, Mastercard, Rupay), net banking, wallets, and Cash on Delivery for eligible orders." },
  { q: "How can I track my order?", a: "Go to My Orders in your account, or use the Track Order link in the top bar with your order ID and email." },
  { q: "What is your return policy?", a: "Most products can be returned within 30 days of delivery. Some categories like grocery and innerwear are non-returnable. Check the product page for details." },
  { q: "How do I become a seller on Xelnova?", a: "Click 'Sell on Xelnova' at the top of the page, register with your business details, upload your documents, and start listing products once verified." },
  { q: "Is Cash on Delivery available?", a: "Yes, COD is available for most orders under ₹50,000 at eligible pin codes. Select COD as your payment method during checkout." },
  { q: "How do I cancel an order?", a: "You can cancel an order before it ships from My Orders page. Once shipped, you can reject the delivery or initiate a return after delivery." },
  { q: "How long does delivery take?", a: "Standard delivery takes 3-7 business days depending on your location. Express delivery (where available) takes 1-2 business days." },
];

function FaqItem({ faq, index }: { faq: typeof faqs[0]; index: number }) {
  const [open, setOpen] = useState(false);
  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.05 }}
      className="border-b border-border-light last:border-0"
    >
      <button onClick={() => setOpen(!open)} className="w-full flex items-center justify-between py-5 text-left group">
        <span className="font-medium text-text-primary group-hover:text-primary-700 transition-colors pr-4">{faq.q}</span>
        <ChevronDown className={cn("w-5 h-5 text-text-muted flex-shrink-0 transition-transform duration-200", open && "rotate-180")} />
      </button>
      <AnimatePresence>
        {open && (
          <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} transition={{ duration: 0.2 }} className="overflow-hidden">
            <p className="text-sm text-text-secondary pb-5 leading-relaxed">{faq.a}</p>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
}

export default function FaqPage() {
  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-gradient-to-br from-primary-700 to-primary-900 text-white py-16">
        <div className="container mx-auto px-4 text-center">
          <HelpCircle className="w-12 h-12 mx-auto mb-4 text-white/60" />
          <h1 className="text-3xl md:text-4xl font-extrabold mb-3">Frequently Asked Questions</h1>
          <p className="text-white/70 max-w-md mx-auto">Find answers to the most common questions about shopping on Xelnova.</p>
        </div>
      </div>
      <div className="container mx-auto px-4 -mt-6 pb-12">
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm max-w-3xl mx-auto px-6 md:px-8">
          {faqs.map((faq, i) => (
            <FaqItem key={i} faq={faq} index={i} />
          ))}
        </div>
      </div>
    </div>
  );
}
