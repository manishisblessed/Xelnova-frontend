"use client";

import { motion } from "framer-motion";
import { TrendingUp, Users, Headphones, Shield, ArrowRight, CheckCircle2, Store } from "lucide-react";
import { Button } from "@/components/ui/button";
import Link from "next/link";

const benefits = [
  { icon: Users, title: "Reach Millions", desc: "Access our growing customer base across India" },
  { icon: TrendingUp, title: "Grow Revenue", desc: "Powerful analytics and tools to boost your sales" },
  { icon: Headphones, title: "Seller Support", desc: "Dedicated account manager and 24/7 help" },
  { icon: Shield, title: "Secure Payments", desc: "Timely payouts directly to your bank account" },
];

const steps = [
  { step: "1", title: "Register", desc: "Sign up with your business details" },
  { step: "2", title: "Upload Documents", desc: "Verify your business identity" },
  { step: "3", title: "List Products", desc: "Add your products with images & pricing" },
  { step: "4", title: "Start Selling", desc: "Receive orders and grow your business" },
];

export default function SellerLandingPage() {
  return (
    <div className="min-h-screen">
      {/* Hero */}
      <div className="bg-gradient-to-br from-primary-800 via-primary-700 to-primary-900 text-white py-20 relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute top-10 right-10 w-72 h-72 rounded-full bg-accent-400 blur-3xl" />
          <div className="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-primary-400 blur-3xl" />
        </div>
        <div className="container mx-auto px-4 text-center relative z-10">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
            <div className="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center mx-auto mb-6">
              <Store className="w-8 h-8" />
            </div>
            <h1 className="text-3xl md:text-5xl font-extrabold mb-4 leading-tight">Sell on Xelnova</h1>
            <p className="text-lg text-white/70 max-w-2xl mx-auto mb-8">Join thousands of sellers and reach millions of customers across India. Start your e-commerce journey today.</p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Button size="lg" className="bg-white text-primary-700 hover:bg-primary-50 shadow-xl px-8 py-3.5 text-base">
                Start Selling <ArrowRight className="w-5 h-5" />
              </Button>
              <Button size="lg" variant="outline" className="border-white/30 text-white hover:bg-white/10 px-8 py-3.5 text-base">
                Learn More
              </Button>
            </div>
          </motion.div>
        </div>
      </div>

      {/* Benefits */}
      <div className="container mx-auto px-4 -mt-10 pb-16">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {benefits.map(({ icon: Icon, title, desc }, i) => (
            <motion.div key={title} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.1 }} className="bg-white rounded-2xl border border-border/60 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
              <div className="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center mx-auto mb-3">
                <Icon className="w-6 h-6 text-primary-600" />
              </div>
              <h3 className="font-bold text-text-primary mb-1">{title}</h3>
              <p className="text-xs text-text-muted">{desc}</p>
            </motion.div>
          ))}
        </div>
      </div>

      {/* How It Works */}
      <div className="bg-white py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-2xl md:text-3xl font-extrabold text-text-primary text-center mb-12">How It Works</h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            {steps.map((s, i) => (
              <motion.div key={s.step} initial={{ opacity: 0, y: 16 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1 }} className="text-center relative">
                <div className="w-14 h-14 rounded-2xl bg-primary-600 text-white flex items-center justify-center mx-auto mb-4 text-xl font-extrabold shadow-lg shadow-primary-600/20">
                  {s.step}
                </div>
                <h3 className="font-bold text-text-primary mb-1">{s.title}</h3>
                <p className="text-xs text-text-muted">{s.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* Why Sellers Love Us */}
      <div className="container mx-auto px-4 py-16">
        <div className="bg-gradient-to-br from-surface-warm to-white rounded-3xl border border-border/60 p-8 md:p-12">
          <h2 className="text-2xl font-extrabold text-text-primary mb-8 text-center">Why Sellers Love Xelnova</h2>
          <div className="grid md:grid-cols-2 gap-4 max-w-3xl mx-auto">
            {[
              "0% commission for the first 3 months",
              "Easy product listing with bulk upload",
              "Real-time order & inventory management",
              "Weekly payouts to your bank account",
              "Dedicated seller support team",
              "Marketing tools & promotional features",
              "Detailed analytics & sales reports",
              "Pan-India logistics support",
            ].map((item, i) => (
              <motion.div key={i} initial={{ opacity: 0, x: -10 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.05 }} className="flex items-center gap-3">
                <CheckCircle2 className="w-5 h-5 text-primary-600 flex-shrink-0" />
                <span className="text-sm text-text-secondary">{item}</span>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* CTA */}
      <div className="bg-surface-dark text-white py-16">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-2xl md:text-3xl font-extrabold mb-3">Ready to Start Selling?</h2>
          <p className="text-white/60 mb-8 max-w-md mx-auto">Join Xelnova today and take your business to the next level. Registration is free and takes just 5 minutes.</p>
          <Link href="/seller/register">
            <Button size="lg" className="px-10 py-3.5 text-base">
              Register as Seller <ArrowRight className="w-5 h-5" />
            </Button>
          </Link>
        </div>
      </div>
    </div>
  );
}
