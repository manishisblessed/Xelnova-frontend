"use client";

import { motion } from "framer-motion";
import { Mail, Phone, MapPin, Send } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function ContactPage() {
  return (
    <div className="min-h-screen bg-surface-raised">
      <div className="bg-gradient-to-br from-primary-700 to-primary-900 text-white py-16">
        <div className="container mx-auto px-4 text-center">
          <motion.h1 initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} className="text-3xl md:text-4xl font-extrabold mb-3">Get in Touch</motion.h1>
          <p className="text-white/70 max-w-md mx-auto">Have a question? We&apos;d love to hear from you. Send us a message and we&apos;ll respond as soon as possible.</p>
        </div>
      </div>
      <div className="container mx-auto px-4 -mt-8 pb-12">
        <div className="grid md:grid-cols-3 gap-6 mb-8">
          {[
            { icon: Phone, title: "Call Us", info: "1800-123-4567", sub: "Mon-Sat, 9am-6pm" },
            { icon: Mail, title: "Email Us", info: "support@xelnova.in", sub: "We reply within 24 hours" },
            { icon: MapPin, title: "Visit Us", info: "New Delhi, India", sub: "110077" },
          ].map(({ icon: Icon, title, info, sub }, i) => (
            <motion.div key={title} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.1 }} className="bg-white rounded-2xl border border-border/60 p-6 text-center shadow-sm">
              <div className="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center mx-auto mb-3">
                <Icon className="w-5 h-5 text-primary-600" />
              </div>
              <h3 className="font-semibold text-text-primary mb-1">{title}</h3>
              <p className="text-sm font-medium text-primary-600">{info}</p>
              <p className="text-xs text-text-muted mt-0.5">{sub}</p>
            </motion.div>
          ))}
        </div>
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }} className="bg-white rounded-2xl border border-border/60 shadow-sm max-w-2xl mx-auto p-8">
          <h2 className="text-xl font-bold text-text-primary mb-6">Send us a Message</h2>
          <form className="space-y-4">
            <div className="grid md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-text-primary mb-1.5">Name</label>
                <input type="text" placeholder="Your name" className="w-full px-4 py-2.5 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-text-primary mb-1.5">Email</label>
                <input type="email" placeholder="your@email.com" className="w-full px-4 py-2.5 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" />
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-text-primary mb-1.5">Subject</label>
              <input type="text" placeholder="How can we help?" className="w-full px-4 py-2.5 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" />
            </div>
            <div>
              <label className="block text-sm font-medium text-text-primary mb-1.5">Message</label>
              <textarea rows={5} placeholder="Write your message..." className="w-full px-4 py-2.5 rounded-xl border border-border text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 resize-none" />
            </div>
            <Button size="lg"><Send className="w-4 h-4" /> Send Message</Button>
          </form>
        </motion.div>
      </div>
    </div>
  );
}
