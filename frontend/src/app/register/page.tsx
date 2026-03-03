"use client";

import { useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { User, Phone, Mail, ArrowRight, ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function RegisterPage() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");

  return (
    <div className="min-h-[80vh] flex items-center justify-center bg-surface-raised px-4 py-12">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="w-full max-w-md">
        <div className="bg-white rounded-3xl shadow-xl shadow-black/5 border border-border/60 overflow-hidden">
          <div className="bg-gradient-to-br from-primary-600 to-primary-800 p-8 text-white text-center">
            <div className="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-black">X</span>
            </div>
            <h1 className="text-2xl font-bold">Create Account</h1>
            <p className="text-white/70 mt-1 text-sm">Join Xelnova for exclusive deals</p>
          </div>

          <div className="p-8 space-y-4">
            <div>
              <label className="block text-sm font-medium text-text-primary mb-1.5">Full Name</label>
              <div className="relative">
                <User className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
                <input type="text" value={name} onChange={(e) => setName(e.target.value)} placeholder="Enter your name" className="w-full pl-10 pr-4 py-3 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-text-primary mb-1.5">Email Address</label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
                <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="Enter email" className="w-full pl-10 pr-4 py-3 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-text-primary mb-1.5">Mobile Number</label>
              <div className="relative">
                <Phone className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
                <input type="tel" value={phone} onChange={(e) => setPhone(e.target.value.replace(/\D/g, "").slice(0, 10))} placeholder="10-digit mobile number" className="w-full pl-10 pr-4 py-3 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" />
              </div>
            </div>

            <Button className="w-full py-3" size="lg">
              Create Account <ArrowRight className="w-4 h-4" />
            </Button>

            <p className="text-xs text-text-muted text-center">
              By signing up, you agree to our <Link href="/terms" className="text-primary-600 hover:underline">Terms</Link> and <Link href="/privacy" className="text-primary-600 hover:underline">Privacy Policy</Link>.
            </p>

            <div className="text-center pt-2 border-t border-border-light">
              <p className="text-sm text-text-muted">
                Already have an account? <Link href="/login" className="text-primary-600 font-semibold hover:underline">Sign In</Link>
              </p>
            </div>

            <div className="flex items-center justify-center gap-1.5 text-xs text-text-muted">
              <ShieldCheck className="w-3.5 h-3.5" /> Your data is safe and secure
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  );
}
