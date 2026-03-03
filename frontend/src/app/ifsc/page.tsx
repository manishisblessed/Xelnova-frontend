"use client";

import { useState, useRef, useCallback } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  Search,
  Building2,
  MapPin,
  Phone,
  Copy,
  Check,
  Landmark,
  ArrowRight,
  ShieldCheck,
  Zap,
  Globe,
  CreditCard,
  AlertCircle,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface IfscResult {
  IFSC: string;
  BANK: string;
  BRANCH: string;
  ADDRESS: string;
  CITY: string;
  DISTRICT: string;
  STATE: string;
  CONTACT: string;
  BANKCODE: string;
  MICR: string;
  RTGS: boolean;
  NEFT: boolean;
  IMPS: boolean;
  UPI: boolean;
  SWIFT?: string;
  ISO3166?: string;
  CENTRE?: string;
}

const RAZORPAY_API = "https://ifsc.razorpay.com";

const recentSearches = [
  { code: "SBIN0001234", label: "SBI" },
  { code: "HDFC0000001", label: "HDFC" },
  { code: "ICIC0000001", label: "ICICI" },
  { code: "PUNB0244200", label: "PNB" },
];

export default function IfscLookupPage() {
  const [code, setCode] = useState("");
  const [result, setResult] = useState<IfscResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleLookup = useCallback(
    async (ifscCode?: string) => {
      const searchCode = (ifscCode ?? code).trim().toUpperCase().replace(/\s+/g, "");

      if (searchCode.length !== 11) {
        setError("IFSC code must be exactly 11 characters (e.g. HDFC0001234)");
        setResult(null);
        return;
      }

      if (searchCode[4] !== "0") {
        setError("The 5th character of an IFSC code must be 0");
        setResult(null);
        return;
      }

      setError(null);
      setResult(null);
      setLoading(true);

      try {
        const res = await fetch(`${RAZORPAY_API}/${searchCode}`);
        if (!res.ok) {
          if (res.status === 404) {
            setError("IFSC code not found. Please check and try again.");
          } else {
            setError("Something went wrong. Please try again.");
          }
          return;
        }
        const data: IfscResult = await res.json();
        setResult(data);
        setCode(searchCode);
      } catch {
        setError("Network error. Please check your connection and try again.");
      } finally {
        setLoading(false);
      }
    },
    [code]
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    handleLookup();
  };

  const handleQuickSearch = (ifscCode: string) => {
    setCode(ifscCode);
    handleLookup(ifscCode);
  };

  const copyToClipboard = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    setCopied(label);
    setTimeout(() => setCopied(null), 2000);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 11);
    setCode(val);
    if (error) setError(null);
  };

  const supportedServices = result
    ? [
        { key: "NEFT", enabled: result.NEFT, icon: ArrowRight, color: "text-blue-600 bg-blue-50 border-blue-200" },
        { key: "RTGS", enabled: result.RTGS, icon: Zap, color: "text-purple-600 bg-purple-50 border-purple-200" },
        { key: "IMPS", enabled: result.IMPS, icon: Globe, color: "text-emerald-600 bg-emerald-50 border-emerald-200" },
        { key: "UPI", enabled: result.UPI, icon: CreditCard, color: "text-orange-600 bg-orange-50 border-orange-200" },
      ]
    : [];

  return (
    <div className="min-h-screen bg-surface-raised">
      {/* Hero */}
      <div className="bg-gradient-to-br from-primary-700 via-primary-800 to-primary-900 text-white relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/20 blur-3xl" />
          <div className="absolute -bottom-32 -left-32 w-[500px] h-[500px] rounded-full bg-accent-400/20 blur-3xl" />
        </div>

        <div className="container mx-auto px-4 pt-12 pb-20 relative">
          <div className="text-center max-w-2xl mx-auto">
            <motion.div
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/15 rounded-full px-4 py-1.5 text-xs font-medium text-white/80 mb-5"
            >
              <Landmark className="w-3.5 h-3.5" />
              Powered by Razorpay IFSC Toolkit
            </motion.div>

            <motion.h1
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.05 }}
              className="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 tracking-tight"
            >
              IFSC Code Lookup
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="text-white/60 text-base md:text-lg max-w-lg mx-auto mb-8"
            >
              Instantly find bank branch details, address, and supported payment modes using any IFSC code.
            </motion.p>

            {/* Search Box */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.15 }}
              className="max-w-xl mx-auto"
            >
              <form onSubmit={handleSubmit}>
                <div
                  className={cn(
                    "relative flex items-center rounded-2xl border bg-white/[0.08] backdrop-blur-xl transition-all duration-300",
                    error
                      ? "border-red-400/60 ring-4 ring-red-400/10"
                      : "border-white/20 focus-within:border-white/40 focus-within:ring-4 focus-within:ring-white/10 focus-within:bg-white/[0.12]"
                  )}
                >
                  <Search className="absolute left-4 w-5 h-5 text-white/40" />
                  <input
                    ref={inputRef}
                    type="text"
                    value={code}
                    onChange={handleInputChange}
                    onKeyDown={(e) => e.key === "Enter" && handleLookup()}
                    placeholder="Enter IFSC code (e.g. HDFC0001234)"
                    className="w-full bg-transparent py-4 pl-12 pr-36 text-white text-base md:text-lg font-mono tracking-widest placeholder:text-white/30 placeholder:tracking-normal placeholder:font-sans focus:outline-none"
                    autoFocus
                    spellCheck={false}
                    autoComplete="off"
                  />
                  <Button
                    type="submit"
                    size="lg"
                    disabled={loading || code.length < 11}
                    loading={loading}
                    className="absolute right-2 bg-white text-primary-700 hover:bg-white/90 hover:text-primary-800 shadow-lg shadow-black/10 font-bold"
                  >
                    {loading ? "Looking up..." : "Search"}
                  </Button>
                </div>
              </form>

              {/* Error */}
              <AnimatePresence>
                {error && (
                  <motion.div
                    initial={{ opacity: 0, y: -4 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -4 }}
                    className="flex items-center gap-2 mt-3 text-sm text-red-300 justify-center"
                  >
                    <AlertCircle className="w-4 h-4 flex-shrink-0" />
                    {error}
                  </motion.div>
                )}
              </AnimatePresence>

              {/* Character count */}
              {code.length > 0 && !error && (
                <div className="mt-2 text-xs text-white/30 text-center font-mono">
                  {code.length}/11 characters
                </div>
              )}

              {/* Quick searches */}
              {!result && !loading && (
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  transition={{ delay: 0.25 }}
                  className="flex items-center justify-center gap-2 mt-5 flex-wrap"
                >
                  <span className="text-white/30 text-xs">Try:</span>
                  {recentSearches.map((s) => (
                    <button
                      key={s.code}
                      onClick={() => handleQuickSearch(s.code)}
                      className="px-3 py-1 rounded-full bg-white/[0.08] border border-white/10 text-white/60 text-xs font-medium hover:bg-white/15 hover:text-white/80 transition-all"
                    >
                      {s.label}
                    </button>
                  ))}
                </motion.div>
              )}
            </motion.div>
          </div>
        </div>
      </div>

      {/* Result */}
      <div className="container mx-auto px-4 -mt-6 pb-16">
        <AnimatePresence mode="wait">
          {loading && (
            <motion.div
              key="loading"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="max-w-3xl mx-auto"
            >
              <div className="bg-white rounded-2xl border border-border/60 shadow-sm p-8 animate-pulse">
                <div className="flex items-center gap-4 mb-6">
                  <div className="w-14 h-14 rounded-2xl bg-gray-100" />
                  <div className="flex-1">
                    <div className="h-5 bg-gray-100 rounded w-1/3 mb-2" />
                    <div className="h-4 bg-gray-100 rounded w-1/5" />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="h-16 bg-gray-50 rounded-xl" />
                  ))}
                </div>
              </div>
            </motion.div>
          )}

          {result && !loading && (
            <motion.div
              key="result"
              initial={{ opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
              className="max-w-3xl mx-auto"
            >
              <div className="bg-white rounded-2xl border border-border/60 shadow-xl shadow-black/[0.03] overflow-hidden">
                {/* Bank header */}
                <div className="bg-gradient-to-r from-gray-50 to-white px-6 md:px-8 py-6 border-b border-border-light">
                  <div className="flex items-start gap-4">
                    <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary-500/20">
                      <Building2 className="w-7 h-7 text-white" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <h2 className="text-xl md:text-2xl font-extrabold text-text-primary leading-tight">
                        {result.BANK}
                      </h2>
                      <p className="text-sm text-text-muted mt-1">{result.BRANCH}</p>
                    </div>
                    <div className="hidden sm:block flex-shrink-0">
                      <CopyButton
                        text={result.IFSC}
                        label="IFSC"
                        copied={copied}
                        onCopy={copyToClipboard}
                      />
                    </div>
                  </div>
                </div>

                {/* IFSC highlight strip */}
                <div className="px-6 md:px-8 py-4 bg-primary-50/50 border-b border-primary-100/60 flex items-center justify-between flex-wrap gap-3">
                  <div className="flex items-center gap-3">
                    <span className="text-xs font-semibold text-primary-600 uppercase tracking-wider">IFSC</span>
                    <span className="font-mono text-xl md:text-2xl font-extrabold text-primary-700 tracking-[0.15em]">
                      {result.IFSC}
                    </span>
                  </div>
                  <div className="sm:hidden">
                    <CopyButton
                      text={result.IFSC}
                      label="IFSC"
                      copied={copied}
                      onCopy={copyToClipboard}
                    />
                  </div>
                </div>

                {/* Details grid */}
                <div className="px-6 md:px-8 py-6">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <DetailCard
                      icon={<Building2 className="w-4 h-4" />}
                      label="Bank Code"
                      value={result.BANKCODE}
                      copiable
                      copied={copied}
                      onCopy={copyToClipboard}
                    />
                    <DetailCard
                      icon={<Landmark className="w-4 h-4" />}
                      label="Branch"
                      value={result.BRANCH}
                    />
                    <DetailCard
                      icon={<MapPin className="w-4 h-4" />}
                      label="City"
                      value={[result.CITY, result.DISTRICT].filter(Boolean).join(", ") || "—"}
                    />
                    <DetailCard
                      icon={<MapPin className="w-4 h-4" />}
                      label="State"
                      value={result.STATE}
                    />
                    {result.MICR && (
                      <DetailCard
                        icon={<CreditCard className="w-4 h-4" />}
                        label="MICR Code"
                        value={result.MICR}
                        copiable
                        copied={copied}
                        onCopy={copyToClipboard}
                      />
                    )}
                    {result.CONTACT && (
                      <DetailCard
                        icon={<Phone className="w-4 h-4" />}
                        label="Contact"
                        value={result.CONTACT}
                      />
                    )}
                    {result.SWIFT && (
                      <DetailCard
                        icon={<Globe className="w-4 h-4" />}
                        label="SWIFT Code"
                        value={result.SWIFT}
                        copiable
                        copied={copied}
                        onCopy={copyToClipboard}
                      />
                    )}
                    {result.CENTRE && (
                      <DetailCard
                        icon={<Building2 className="w-4 h-4" />}
                        label="Centre"
                        value={result.CENTRE}
                      />
                    )}
                  </div>

                  {/* Address */}
                  {result.ADDRESS && (
                    <div className="mt-5 p-4 rounded-xl bg-gray-50 border border-border-light">
                      <div className="flex items-start gap-3">
                        <MapPin className="w-4 h-4 text-text-muted mt-0.5 flex-shrink-0" />
                        <div className="flex-1">
                          <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Full Address</p>
                          <p className="text-sm text-text-secondary leading-relaxed">{result.ADDRESS}</p>
                        </div>
                        <button
                          onClick={() => copyToClipboard(result.ADDRESS, "Address")}
                          className="flex-shrink-0 p-1.5 rounded-lg hover:bg-gray-200 transition-colors"
                          title="Copy address"
                        >
                          {copied === "Address" ? (
                            <Check className="w-3.5 h-3.5 text-primary-600" />
                          ) : (
                            <Copy className="w-3.5 h-3.5 text-text-muted" />
                          )}
                        </button>
                      </div>
                    </div>
                  )}
                </div>

                {/* Supported services */}
                <div className="px-6 md:px-8 pb-6">
                  <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">
                    Payment Services
                  </p>
                  <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    {supportedServices.map((svc) => (
                      <motion.div
                        key={svc.key}
                        initial={{ opacity: 0, scale: 0.9 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ delay: supportedServices.indexOf(svc) * 0.05 }}
                        className={cn(
                          "flex items-center gap-3 p-3.5 rounded-xl border transition-all",
                          svc.enabled ? svc.color : "bg-gray-50 border-gray-200 opacity-50"
                        )}
                      >
                        <svc.icon className="w-5 h-5 flex-shrink-0" />
                        <div>
                          <p className="text-sm font-bold">{svc.key}</p>
                          <p className="text-[10px] font-medium opacity-70">
                            {svc.enabled ? "Supported" : "Not Available"}
                          </p>
                        </div>
                      </motion.div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Search again prompt */}
              <div className="text-center mt-6">
                <button
                  onClick={() => {
                    setResult(null);
                    setCode("");
                    inputRef.current?.focus();
                  }}
                  className="text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors inline-flex items-center gap-1.5"
                >
                  <Search className="w-4 h-4" />
                  Search another IFSC code
                </button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {/* Info section (shown when no result) */}
        {!result && !loading && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            className="max-w-4xl mx-auto mt-8"
          >
            {/* Feature cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
              {[
                {
                  icon: Zap,
                  title: "Instant Lookup",
                  desc: "Get bank details in milliseconds using the Razorpay IFSC database.",
                },
                {
                  icon: ShieldCheck,
                  title: "Verified Data",
                  desc: "Data sourced from RBI and validated against official IFSC records.",
                },
                {
                  icon: Globe,
                  title: "Complete Coverage",
                  desc: "Covers all Indian banks — public, private, cooperative, and regional.",
                },
              ].map(({ icon: Icon, title, desc }, i) => (
                <motion.div
                  key={title}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.35 + i * 0.08 }}
                  className="bg-white rounded-2xl border border-border/60 p-6 shadow-sm hover:shadow-md transition-shadow"
                >
                  <div className="w-11 h-11 rounded-xl bg-primary-50 flex items-center justify-center mb-3">
                    <Icon className="w-5 h-5 text-primary-600" />
                  </div>
                  <h3 className="font-bold text-text-primary mb-1">{title}</h3>
                  <p className="text-sm text-text-muted leading-relaxed">{desc}</p>
                </motion.div>
              ))}
            </div>

            {/* What is IFSC */}
            <div className="bg-white rounded-2xl border border-border/60 shadow-sm p-6 md:p-8">
              <h2 className="text-xl font-bold text-text-primary mb-4">What is an IFSC Code?</h2>
              <div className="space-y-3 text-sm text-text-secondary leading-relaxed">
                <p>
                  <strong className="text-text-primary">IFSC (Indian Financial System Code)</strong> is an
                  11-character alphanumeric code that uniquely identifies a bank branch participating in
                  electronic payment systems in India such as NEFT, RTGS, and IMPS.
                </p>
                <p>
                  The code is structured as: the first 4 characters represent the bank, the 5th character
                  is always <strong className="text-text-primary">0</strong> (reserved for future use),
                  and the last 6 characters identify the specific branch.
                </p>
              </div>

              {/* Visual breakdown */}
              <div className="mt-6 p-4 bg-gray-50 rounded-xl border border-border-light">
                <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">
                  IFSC Code Structure
                </p>
                <div className="flex items-center gap-1 flex-wrap">
                  {["H", "D", "F", "C"].map((char, i) => (
                    <div
                      key={`bank-${i}`}
                      className="w-10 h-12 rounded-lg bg-primary-100 border-2 border-primary-300 flex items-center justify-center font-mono text-lg font-bold text-primary-700"
                    >
                      {char}
                    </div>
                  ))}
                  <div className="w-10 h-12 rounded-lg bg-gray-200 border-2 border-gray-300 flex items-center justify-center font-mono text-lg font-bold text-gray-500">
                    0
                  </div>
                  {["0", "0", "0", "1", "2", "3"].map((char, i) => (
                    <div
                      key={`branch-${i}`}
                      className="w-10 h-12 rounded-lg bg-accent-100 border-2 border-accent-300 flex items-center justify-center font-mono text-lg font-bold text-accent-700"
                    >
                      {char}
                    </div>
                  ))}
                </div>
                <div className="flex items-center gap-4 mt-3 text-xs font-medium">
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-primary-300" />
                    <span className="text-text-muted">Bank Code</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-gray-300" />
                    <span className="text-text-muted">Reserved</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-accent-300" />
                    <span className="text-text-muted">Branch Code</span>
                  </div>
                </div>
              </div>
            </div>
          </motion.div>
        )}
      </div>
    </div>
  );
}

/* ─── Sub-components ─── */

function DetailCard({
  icon,
  label,
  value,
  copiable,
  copied,
  onCopy,
}: {
  icon: React.ReactNode;
  label: string;
  value: string | null;
  copiable?: boolean;
  copied?: string | null;
  onCopy?: (text: string, label: string) => void;
}) {
  if (!value) return null;
  return (
    <div className="group flex items-center gap-3 p-3.5 rounded-xl bg-gray-50/80 border border-border-light hover:bg-gray-50 transition-colors">
      <div className="w-9 h-9 rounded-lg bg-white border border-border flex items-center justify-center text-text-muted flex-shrink-0 shadow-sm">
        {icon}
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">{label}</p>
        <p className="text-sm font-semibold text-text-primary truncate mt-0.5">{value}</p>
      </div>
      {copiable && onCopy && (
        <button
          onClick={() => onCopy(value, label)}
          className="p-1.5 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-gray-200 transition-all"
          title={`Copy ${label}`}
        >
          {copied === label ? (
            <Check className="w-3.5 h-3.5 text-primary-600" />
          ) : (
            <Copy className="w-3.5 h-3.5 text-text-muted" />
          )}
        </button>
      )}
    </div>
  );
}

function CopyButton({
  text,
  label,
  copied,
  onCopy,
}: {
  text: string;
  label: string;
  copied: string | null;
  onCopy: (text: string, label: string) => void;
}) {
  return (
    <button
      onClick={() => onCopy(text, label)}
      className={cn(
        "inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200 border",
        copied === label
          ? "bg-primary-50 border-primary-200 text-primary-700"
          : "bg-white border-border text-text-secondary hover:border-primary-300 hover:text-primary-600"
      )}
    >
      {copied === label ? (
        <>
          <Check className="w-3.5 h-3.5" />
          Copied!
        </>
      ) : (
        <>
          <Copy className="w-3.5 h-3.5" />
          Copy IFSC
        </>
      )}
    </button>
  );
}
