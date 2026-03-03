"use client";

import { useState, useEffect, useCallback } from "react";
import Image from "next/image";
import Link from "next/link";
import { motion, AnimatePresence } from "framer-motion";
import { Minus, Plus, Trash2, ShoppingBag, Tag, ArrowRight, ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { formatPrice } from "@/lib/utils";
import { getCart, updateCartItem, removeCartItem, applyCoupon, removeCoupon } from "@/lib/api";

export default function CartPage() {
  const [cartItems, setCartItems] = useState<Array<{ id: number; name: string; slug: string; image: string; price: number; originalPrice?: number; quantity: number; itemId: number }>>([]);
  const [subtotal, setSubtotal] = useState(0);
  const [discount, setDiscount] = useState(0);
  const [delivery, setDelivery] = useState(0);
  const [total, setTotal] = useState(0);
  const [coupon, setCoupon] = useState("");
  const [couponApplied, setCouponApplied] = useState<{ code: string; name: string; discount: number } | null>(null);
  const [loading, setLoading] = useState(true);
  const [updatingId, setUpdatingId] = useState<number | null>(null);
  const [couponError, setCouponError] = useState<string | null>(null);

  const loadCart = useCallback(async () => {
    setLoading(true);
    try {
      const cart = await getCart();
      setCartItems(cart.items);
      setSubtotal(cart.subtotal);
      setDiscount(cart.discount);
      setDelivery(cart.shippingCharge);
      setTotal(cart.total);
      setCouponApplied(cart.coupon);
    } catch {
      setCartItems([]);
      setSubtotal(0);
      setDiscount(0);
      setDelivery(0);
      setTotal(0);
      setCouponApplied(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const notifyCartUpdated = () => {
    if (typeof window !== "undefined") window.dispatchEvent(new CustomEvent("cart-updated"));
  };

  useEffect(() => {
    loadCart();
  }, [loadCart]);

  const updateQuantity = async (itemId: number, quantity: number) => {
    if (quantity < 1) return;
    setUpdatingId(itemId);
    try {
      const cart = await updateCartItem(itemId, quantity);
      setCartItems(cart.items);
      setSubtotal(cart.subtotal);
      setDiscount(cart.discount);
      setDelivery(cart.shippingCharge);
      setTotal(cart.total);
      setCouponApplied(cart.coupon);
      notifyCartUpdated();
    } catch {
      await loadCart();
    } finally {
      setUpdatingId(null);
    }
  };

  const removeItem = async (itemId: number) => {
    setUpdatingId(itemId);
    try {
      const cart = await removeCartItem(itemId);
      setCartItems(cart.items);
      setSubtotal(cart.subtotal);
      setDiscount(cart.discount);
      setDelivery(cart.shippingCharge);
      setTotal(cart.total);
      setCouponApplied(cart.coupon);
      notifyCartUpdated();
    } catch {
      await loadCart();
    } finally {
      setUpdatingId(null);
    }
  };

  const handleApplyCoupon = async () => {
    if (!coupon.trim()) return;
    setCouponError(null);
    try {
      const cart = await applyCoupon(coupon.trim());
      setDiscount(cart.discount);
      setTotal(cart.total);
      setCouponApplied(cart.coupon);
      notifyCartUpdated();
    } catch (e) {
      setCouponError(e instanceof Error ? e.message : "Invalid coupon");
    }
  };

  const handleRemoveCoupon = async () => {
    try {
      const cart = await removeCoupon();
      setDiscount(cart.discount);
      setTotal(cart.total);
      setCouponApplied(null);
      setCoupon("");
      setCouponError(null);
      notifyCartUpdated();
    } catch {
      await loadCart();
    }
  };

  const displayDiscount = discount;
  const displayDelivery = delivery;
  const displayTotal = total;

  if (loading && cartItems.length === 0) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center px-4">
        <div className="w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full animate-spin" />
        <p className="mt-4 text-text-muted">Loading cart...</p>
      </div>
    );
  }

  if (cartItems.length === 0) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center px-4">
        <div className="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
          <ShoppingBag className="w-10 h-10 text-text-muted" />
        </div>
        <h2 className="text-xl font-bold text-text-primary mb-2">Your cart is empty</h2>
        <p className="text-text-muted mb-6">Looks like you haven&apos;t added anything yet.</p>
        <Link href="/products">
          <Button>Continue Shopping</Button>
        </Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600">Home</a>
            <span className="mx-2">/</span>
            <span className="text-text-primary font-medium">Shopping Cart ({cartItems.length})</span>
          </nav>
        </div>
      </div>

      <div className="container mx-auto px-4 py-6">
        <div className="flex flex-col lg:flex-row gap-6">
          {/* Cart Items */}
          <div className="flex-1">
            <div className="bg-white rounded-2xl border border-border/60 shadow-sm overflow-hidden">
              <div className="p-4 border-b border-border-light">
                <h2 className="font-bold text-text-primary">Shopping Cart ({cartItems.length} items)</h2>
              </div>
              <AnimatePresence>
                {cartItems.map((item) => (
                  <motion.div
                    key={item.itemId}
                    exit={{ opacity: 0, height: 0 }}
                    transition={{ duration: 0.2 }}
                    className="flex gap-4 p-4 border-b border-border-light last:border-0"
                  >
                    <Link href={`/products/${item.slug}`} className="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden bg-surface-raised">
                      <Image src={item.image} alt={item.name} width={96} height={96} className="w-full h-full object-cover" />
                    </Link>
                    <div className="flex-1 min-w-0">
                      <Link href={`/products/${item.slug}`}>
                        <h3 className="font-medium text-text-primary text-sm line-clamp-2 hover:text-primary-700 transition-colors">{item.name}</h3>
                      </Link>
                      <div className="flex items-baseline gap-2 mt-2">
                        <span className="font-bold text-text-primary">{formatPrice(item.price)}</span>
                        {item.originalPrice && (
                          <span className="text-xs text-text-muted line-through">{formatPrice(item.originalPrice)}</span>
                        )}
                      </div>
                      <div className="flex items-center justify-between mt-3">
                        <div className="flex items-center border border-border rounded-lg overflow-hidden">
                          <button
                            onClick={() => updateQuantity(item.itemId, Math.max(1, item.quantity - 1))}
                            disabled={updatingId === item.itemId || item.quantity <= 1}
                            className="w-8 h-8 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50"
                          >
                            <Minus className="w-3 h-3" />
                          </button>
                          <span className="w-8 h-8 flex items-center justify-center text-sm font-semibold border-x border-border">{item.quantity}</span>
                          <button
                            onClick={() => updateQuantity(item.itemId, item.quantity + 1)}
                            disabled={updatingId === item.itemId}
                            className="w-8 h-8 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50"
                          >
                            <Plus className="w-3 h-3" />
                          </button>
                        </div>
                        <button
                          onClick={() => removeItem(item.itemId)}
                          disabled={updatingId === item.itemId}
                          className="text-text-muted hover:text-red-500 transition-colors p-1 disabled:opacity-50"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </AnimatePresence>
            </div>
          </div>

          {/* Order Summary */}
          <div className="lg:w-96">
            <div className="bg-white rounded-2xl border border-border/60 shadow-sm sticky top-44">
              <div className="p-4 border-b border-border-light">
                <h3 className="font-bold text-text-primary">Order Summary</h3>
              </div>
              <div className="p-4 space-y-3 text-sm">
                <div className="flex justify-between"><span className="text-text-secondary">Subtotal</span><span className="font-medium">{formatPrice(subtotal)}</span></div>
                {couponApplied ? (
                  <div className="flex justify-between items-center">
                    <span className="text-text-secondary">Coupon ({couponApplied.code})</span>
                    <span className="font-medium text-primary-600">−{formatPrice(couponApplied.discount)}</span>
                    <button type="button" onClick={handleRemoveCoupon} className="text-xs text-red-600 hover:underline">Remove</button>
                  </div>
                ) : null}
                {displayDiscount > 0 && !couponApplied && <div className="flex justify-between"><span className="text-text-secondary">Discount</span><span className="font-medium text-primary-600">−{formatPrice(displayDiscount)}</span></div>}
                <div className="flex justify-between"><span className="text-text-secondary">Delivery</span><span className="font-medium">{displayDelivery === 0 ? <span className="text-primary-600">Free</span> : formatPrice(displayDelivery)}</span></div>

                {/* Coupon */}
                <div className="pt-2">
                  {couponError && <p className="text-red-600 text-xs mb-1">{couponError}</p>}
                  <div className="flex gap-2">
                    <div className="flex-1 relative">
                      <Tag className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
                      <input
                        type="text"
                        value={coupon}
                        onChange={(e) => setCoupon(e.target.value)}
                        placeholder="Coupon code"
                        className="w-full pl-9 pr-3 py-2 rounded-lg border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                      />
                    </div>
                    <Button variant="outline" size="sm" onClick={handleApplyCoupon} disabled={!coupon.trim()}>Apply</Button>
                  </div>
                </div>

                <div className="border-t border-border-light pt-3 flex justify-between">
                  <span className="font-bold text-text-primary text-base">Total</span>
                  <span className="font-extrabold text-lg text-text-primary">{formatPrice(displayTotal)}</span>
                </div>
              </div>
              <div className="p-4 pt-0">
                <Link href="/checkout">
                  <Button className="w-full py-3" size="lg">
                    Proceed to Checkout <ArrowRight className="w-4 h-4" />
                  </Button>
                </Link>
                <div className="flex items-center justify-center gap-1.5 mt-3 text-xs text-text-muted">
                  <ShieldCheck className="w-3.5 h-3.5" /> Secure checkout with SSL encryption
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
