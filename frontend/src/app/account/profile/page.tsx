"use client";

import { motion } from "framer-motion";
import { User, Mail, Phone, MapPin, Edit2, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function ProfilePage() {
  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-white border-b border-border-light">
        <div className="container mx-auto px-4 py-3">
          <nav className="text-sm text-text-muted">
            <a href="/" className="hover:text-primary-600">Home</a>
            <ChevronRight className="w-3 h-3 inline mx-1.5" />
            <span className="text-text-primary font-medium">My Profile</span>
          </nav>
        </div>
      </div>
      <div className="container mx-auto px-4 py-6 max-w-3xl">
        <h1 className="text-2xl font-bold text-text-primary mb-6">My Profile</h1>

        <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} className="bg-white rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6">
          <div className="p-6 flex items-center gap-5">
            <div className="w-16 h-16 rounded-2xl bg-primary-100 flex items-center justify-center">
              <User className="w-7 h-7 text-primary-700" />
            </div>
            <div className="flex-1">
              <h2 className="text-lg font-bold text-text-primary">John Doe</h2>
              <p className="text-sm text-text-muted">Member since Feb 2026</p>
            </div>
            <Button variant="outline" size="sm"><Edit2 className="w-3.5 h-3.5" /> Edit</Button>
          </div>
          <div className="border-t border-border-light px-6 py-4 space-y-4">
            {[
              { icon: Mail, label: "Email", value: "john.doe@email.com" },
              { icon: Phone, label: "Phone", value: "+91 98765 43210" },
            ].map(({ icon: Icon, label, value }) => (
              <div key={label} className="flex items-center gap-3">
                <Icon className="w-4 h-4 text-text-muted" />
                <div>
                  <p className="text-xs text-text-muted">{label}</p>
                  <p className="text-sm font-medium text-text-primary">{value}</p>
                </div>
              </div>
            ))}
          </div>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }} className="bg-white rounded-2xl border border-border/60 shadow-sm overflow-hidden">
          <div className="p-4 border-b border-border-light flex items-center justify-between">
            <h3 className="font-bold text-text-primary flex items-center gap-2"><MapPin className="w-4 h-4 text-primary-600" /> Saved Addresses</h3>
            <Button variant="ghost" size="sm">+ Add New</Button>
          </div>
          <div className="p-4">
            <div className="p-4 rounded-xl border border-primary-200 bg-primary-50/50">
              <div className="flex items-start justify-between">
                <div>
                  <p className="font-semibold text-sm text-text-primary">John Doe <span className="text-xs font-normal text-primary-600 bg-primary-100 px-2 py-0.5 rounded ml-2">Default</span></p>
                  <p className="text-sm text-text-secondary mt-1">122/1, New Line, Bamnoli, Najafgarh</p>
                  <p className="text-sm text-text-secondary">New Delhi, Delhi - 110077</p>
                  <p className="text-sm text-text-muted mt-1">+91 98765 43210</p>
                </div>
                <Button variant="ghost" size="sm"><Edit2 className="w-3.5 h-3.5" /></Button>
              </div>
            </div>
          </div>
        </motion.div>
      </div>
    </div>
  );
}
