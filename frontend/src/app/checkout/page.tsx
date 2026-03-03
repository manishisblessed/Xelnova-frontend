"use client";

import { useState } from "react";
import Image from "next/image";
import { motion } from "framer-motion";
import { MapPin, Plus, CreditCard, ShieldCheck, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";
import { formatPrice } from "@/lib/utils";
import { cn } from "@/lib/utils";
import { MOCK_PRODUCTS } from "@/lib/constants";

const cartItems = MOCK_PRODUCTS.slice(0, 2).map((p, i) => ({ ...p, quantity: i === 0 ? 2 : 1 }));
const addresses = [
  { id: 1, name: "John Doe", phone: "9876543210", line: "122/1, New Line, Bamnoli", city: "New Delhi", state: "Delhi", pin: "110077", isDefault: true },
];

export default function CheckoutPage() {
  const [selectedAddress, setSelectedAddress] = useState(1);
  const [paymentMethod, setPaymentMethod] = useState("online");

  const subtotal = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
  const total = subtotal;

  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600">Home</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <a href="/cart" className="hover:text-primary-600">Cart</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <span className="text-text-primary font-medium">Checkout</span>
          </nav>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <div className="flex flex-col lg:flex-row gap-6">
          <div className="flex-1 space-y-4">
            {/* Address */}
            <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} className="bg-white rounded-2xl border border-border/60 shadow-sm">
              <div className="p-4 border-b border-border-light flex items-center justify-between">
                <h3 className="font-bold text-text-primary flex items-center gap-2"><MapPin className="w-4 h-4 text-primary-600" /> Delivery Address</h3>
                <Button variant="ghost" size="sm"><Plus className="w-4 h-4" /> Add New</Button>
              </div>
              <div className="p-4 space-y-3">
                {addresses.map((addr) => (
                  <label key={addr.id} className={cn("flex gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all", selectedAddress === addr.id ? "border-primary-500 bg-primary-50/50" : "border-border hover:border-gray-300")}>
                    <input type="radio" name="address" checked={selectedAddress === addr.id} onChange={() => setSelectedAddress(addr.id)} className="mt-1 text-primary-600 focus:ring-primary-500" />
                    <div>
                      <p className="font-semibold text-text-primary text-sm">{addr.name} <span className="text-text-muted font-normal ml-2">{addr.phone}</span></p>
                      <p className="text-sm text-text-secondary mt-0.5">{addr.line}, {addr.city}, {addr.state} - {addr.pin}</p>
                      {addr.isDefault && <span className="inline-block mt-1 text-xs font-medium text-primary-600 bg-primary-50 px-2 py-0.5 rounded">Default</span>}
                    </div>
                  </label>
                ))}
              </div>
            </motion.div>

            {/* Payment */}
            <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }} className="bg-white rounded-2xl border border-border/60 shadow-sm">
              <div className="p-4 border-b border-border-light">
                <h3 className="font-bold text-text-primary flex items-center gap-2"><CreditCard className="w-4 h-4 text-primary-600" /> Payment Method</h3>
              </div>
              <div className="p-4 space-y-3">
                {[
                  { id: "online", label: "Pay Online", desc: "UPI, Cards, Net Banking" },
                  { id: "cod", label: "Cash on Delivery", desc: "Pay when you receive" },
                ].map((m) => (
                  <label key={m.id} className={cn("flex gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all", paymentMethod === m.id ? "border-primary-500 bg-primary-50/50" : "border-border hover:border-gray-300")}>
                    <input type="radio" name="payment" checked={paymentMethod === m.id} onChange={() => setPaymentMethod(m.id)} className="mt-0.5 text-primary-600 focus:ring-primary-500" />
                    <div>
                      <p className="font-semibold text-sm text-text-primary">{m.label}</p>
                      <p className="text-xs text-text-muted">{m.desc}</p>
                    </div>
                  </label>
                ))}
              </div>
            </motion.div>
          </div>

          {/* Summary */}
          <div className="lg:w-96">
            <div className="bg-white rounded-2xl border border-border/60 shadow-sm sticky top-44">
              <div className="p-4 border-b border-border-light">
                <h3 className="font-bold text-text-primary">Order Summary</h3>
              </div>
              <div className="p-4 space-y-3">
                {cartItems.map((item) => (
                  <div key={item.id} className="flex gap-3">
                    <div className="w-14 h-14 rounded-lg overflow-hidden bg-surface-raised flex-shrink-0">
                      <Image src={item.image} alt={item.name} width={56} height={56} className="w-full h-full object-cover" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm text-text-primary font-medium line-clamp-1">{item.name}</p>
                      <p className="text-xs text-text-muted">Qty: {item.quantity}</p>
                    </div>
                    <p className="text-sm font-semibold text-text-primary">{formatPrice(item.price * item.quantity)}</p>
                  </div>
                ))}
              </div>
              <div className="p-4 border-t border-border-light space-y-2 text-sm">
                <div className="flex justify-between"><span className="text-text-secondary">Subtotal</span><span className="font-medium">{formatPrice(subtotal)}</span></div>
                <div className="flex justify-between"><span className="text-text-secondary">Delivery</span><span className="font-medium text-primary-600">Free</span></div>
                <div className="flex justify-between pt-2 border-t border-border-light"><span className="font-bold text-base">Total</span><span className="font-extrabold text-lg">{formatPrice(total)}</span></div>
              </div>
              <div className="p-4 pt-0">
                <Button className="w-full py-3.5" size="lg">Place Order</Button>
                <div className="flex items-center justify-center gap-1.5 mt-3 text-xs text-text-muted">
                  <ShieldCheck className="w-3.5 h-3.5" /> 100% secure payment
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
