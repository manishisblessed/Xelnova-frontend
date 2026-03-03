"use client";

import { useState } from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import { Phone, ArrowRight, ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function LoginPage() {
  const [phone, setPhone] = useState("");
  const [otpSent, setOtpSent] = useState(false);
  const [otp, setOtp] = useState("");

  return (
    <div className="min-h-[80vh] flex items-center justify-center bg-surface-raised px-4 py-12">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="w-full max-w-md"
      >
        <div className="bg-white rounded-3xl shadow-xl shadow-black/5 border border-border/60 overflow-hidden">
          {/* Header */}
          <div className="bg-gradient-to-br from-primary-600 to-primary-800 p-8 text-white text-center">
            <div className="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-black">X</span>
            </div>
            <h1 className="text-2xl font-bold">Welcome Back</h1>
            <p className="text-white/70 mt-1 text-sm">Sign in to your Xelnova account</p>
          </div>

          {/* Form */}
          <div className="p-8">
            {!otpSent ? (
              <div>
                <label className="block text-sm font-medium text-text-primary mb-2">Mobile Number</label>
                <div className="relative">
                  <Phone className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-text-muted" />
                  <input
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value.replace(/\D/g, "").slice(0, 10))}
                    placeholder="Enter 10-digit mobile number"
                    className="w-full pl-11 pr-4 py-3 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                  />
                </div>
                <Button
                  onClick={() => phone.length === 10 && setOtpSent(true)}
                  disabled={phone.length !== 10}
                  className="w-full mt-4 py-3"
                  size="lg"
                >
                  Send OTP <ArrowRight className="w-4 h-4" />
                </Button>
              </div>
            ) : (
              <div>
                <p className="text-sm text-text-secondary mb-4">
                  We&apos;ve sent a 6-digit OTP to <span className="font-semibold text-text-primary">+91 {phone}</span>
                  <button onClick={() => setOtpSent(false)} className="text-primary-600 ml-2 font-medium">Change</button>
                </p>
                <label className="block text-sm font-medium text-text-primary mb-2">Enter OTP</label>
                <input
                  type="text"
                  value={otp}
                  onChange={(e) => setOtp(e.target.value.replace(/\D/g, "").slice(0, 6))}
                  placeholder="• • • • • •"
                  maxLength={6}
                  className="w-full text-center tracking-[0.5em] px-4 py-3 rounded-xl border border-border text-lg font-semibold focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                />
                <Button disabled={otp.length !== 6} className="w-full mt-4 py-3" size="lg">
                  Verify & Sign In
                </Button>
                <button className="w-full text-center text-sm text-text-muted hover:text-primary-600 mt-3 transition-colors">
                  Resend OTP
                </button>
              </div>
            )}

            <div className="mt-6 text-center">
              <p className="text-sm text-text-muted">
                New to Xelnova?{" "}
                <Link href="/register" className="text-primary-600 font-semibold hover:underline">Create an account</Link>
              </p>
            </div>

            <div className="flex items-center justify-center gap-1.5 mt-6 pt-4 border-t border-border-light text-xs text-text-muted">
              <ShieldCheck className="w-3.5 h-3.5" /> Your data is safe and secure
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  );
}
