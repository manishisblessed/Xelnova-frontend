"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import { Search, Package } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function TrackOrderPage() {
  const [orderId, setOrderId] = useState("");

  return (
    <div className="min-h-[70vh] flex items-center justify-center bg-surface-raised px-4">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="w-full max-w-md text-center">
        <div className="w-16 h-16 rounded-2xl bg-primary-50 flex items-center justify-center mx-auto mb-6">
          <Package className="w-8 h-8 text-primary-600" />
        </div>
        <h1 className="text-2xl font-extrabold text-text-primary mb-2">Track Your Order</h1>
        <p className="text-text-muted mb-8">Enter your order ID to get real-time tracking updates.</p>
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm p-6">
          <div className="relative mb-4">
            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
            <input
              type="text"
              value={orderId}
              onChange={(e) => setOrderId(e.target.value)}
              placeholder="e.g. XN-20260225-001"
              className="w-full pl-10 pr-4 py-3 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
            />
          </div>
          <Button className="w-full py-3" size="lg" disabled={!orderId.trim()}>
            Track Order
          </Button>
        </div>
      </motion.div>
    </div>
  );
}
