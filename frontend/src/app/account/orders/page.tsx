"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { Package, ChevronRight, Eye } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { formatPrice } from "@/lib/utils";
import { MOCK_PRODUCTS } from "@/lib/constants";

const orders = [
  { id: "XN-20260225-001", date: "Feb 25, 2026", status: "Delivered", statusVariant: "success" as const, items: [MOCK_PRODUCTS[0]], total: 79999 },
  { id: "XN-20260220-002", date: "Feb 20, 2026", status: "In Transit", statusVariant: "info" as const, items: [MOCK_PRODUCTS[2], MOCK_PRODUCTS[3]], total: 37985 },
  { id: "XN-20260210-003", date: "Feb 10, 2026", status: "Processing", statusVariant: "warning" as const, items: [MOCK_PRODUCTS[5]], total: 249990 },
];

export default function OrdersPage() {
  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600">Home</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <span className="text-text-primary font-medium">My Orders</span>
          </nav>
        </div>
      </div>
      <div className="container mx-auto px-4 py-6 max-w-4xl">
        <h1 className="text-2xl font-bold text-text-primary mb-6">My Orders</h1>
        <div className="space-y-4">
          {orders.map((order, i) => (
            <motion.div key={order.id} initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.1 }} className="bg-white rounded-2xl border border-border/60 shadow-sm overflow-hidden">
              <div className="p-4 border-b border-border-light flex flex-wrap items-center justify-between gap-2">
                <div className="flex items-center gap-3">
                  <Package className="w-4 h-4 text-text-muted" />
                  <span className="text-sm font-semibold text-text-primary">{order.id}</span>
                  <span className="text-xs text-text-muted">{order.date}</span>
                </div>
                <Badge variant={order.statusVariant}>{order.status}</Badge>
              </div>
              <div className="p-4">
                {order.items.map((item) => (
                  <div key={item.id} className="flex items-center gap-4">
                    <div className="w-16 h-16 rounded-xl overflow-hidden bg-surface-raised flex-shrink-0">
                      <Image src={item.image} alt={item.name} width={64} height={64} className="w-full h-full object-cover" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-text-primary line-clamp-1">{item.name}</p>
                      <p className="text-xs text-text-muted">{item.brand}</p>
                    </div>
                    <p className="text-sm font-bold text-text-primary">{formatPrice(item.price)}</p>
                  </div>
                ))}
              </div>
              <div className="p-4 bg-surface-raised flex items-center justify-between">
                <span className="text-sm text-text-secondary">Total: <span className="font-bold text-text-primary">{formatPrice(order.total)}</span></span>
                <Link href={`/account/orders/${order.id}`}>
                  <Button variant="ghost" size="sm"><Eye className="w-4 h-4" /> View Details</Button>
                </Link>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </div>
  );
}
