"use client";

import { useState, useRef, useCallback } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  Search,
  Building2,
  MapPin,
  Copy,
  Check,
  ShieldCheck,
  Zap,
  Globe,
  FileText,
  Calendar,
  AlertCircle,
  Briefcase,
  BadgeCheck,
  Scale,
  Hash,
  CircleDot,
  Receipt,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { isValidGstinFormat } from "@/lib/api/gstin";
import type { GstinDetails } from "@/lib/api/gstin";
import { apiGet } from "@/lib/api/client";

const INDIAN_STATES: Record<string, string> = {
  "01": "Jammu & Kashmir", "02": "Himachal Pradesh", "03": "Punjab", "04": "Chandigarh",
  "05": "Uttarakhand", "06": "Haryana", "07": "Delhi", "08": "Rajasthan", "09": "Uttar Pradesh",
  "10": "Bihar", "11": "Sikkim", "12": "Arunachal Pradesh", "13": "Nagaland", "14": "Manipur",
  "15": "Mizoram", "16": "Tripura", "17": "Meghalaya", "18": "Assam", "19": "West Bengal",
  "20": "Jharkhand", "21": "Odisha", "22": "Chhattisgarh", "23": "Madhya Pradesh",
  "24": "Gujarat", "25": "Daman & Diu", "26": "Dadra & Nagar Haveli", "27": "Maharashtra",
  "28": "Andhra Pradesh", "29": "Karnataka", "30": "Goa", "31": "Lakshadweep",
  "32": "Kerala", "33": "Tamil Nadu", "34": "Puducherry", "35": "Andaman & Nicobar",
  "36": "Telangana", "37": "Andhra Pradesh (New)", "38": "Ladakh",
};

export default function GstinLookupPage() {
  const [code, setCode] = useState("");
  const [result, setResult] = useState<GstinDetails | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleLookup = useCallback(
    async (gstinCode?: string) => {
      const searchCode = (gstinCode ?? code).trim().toUpperCase().replace(/\s+/g, "");

      if (searchCode.length !== 15) {
        setError("GSTIN must be exactly 15 characters");
        setResult(null);
        return;
      }

      if (!isValidGstinFormat(searchCode)) {
        setError("Invalid GSTIN format. It should be like 29AAGCR4375J1ZU");
        setResult(null);
        return;
      }

      setError(null);
      setResult(null);
      setLoading(true);

      try {
        const res = await apiGet<GstinDetails>(`/api/v1/gstin/${searchCode}`);
        if (res.success && res.data) {
          setResult(res.data);
          setCode(searchCode);
        } else {
          setError((res as { message?: string }).message || "GSTIN not found");
        }
      } catch (e) {
        const msg = e instanceof Error ? e.message : "Something went wrong";
        if (msg.includes("not found") || msg.includes("404")) {
          setError("GSTIN not found. Please check and try again.");
        } else if (msg.includes("format")) {
          setError("Invalid GSTIN format.");
        } else {
          setError(msg);
        }
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

  const handleQuickSearch = (gstinCode: string) => {
    setCode(gstinCode);
    handleLookup(gstinCode);
  };

  const copyToClipboard = (text: string, label: string) => {
    navigator.clipboard.writeText(text);
    setCopied(label);
    setTimeout(() => setCopied(null), 2000);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 15);
    setCode(val);
    if (error) setError(null);
  };

  const stateCode = result?.gstin?.slice(0, 2);
  const stateName = stateCode ? INDIAN_STATES[stateCode] : null;

  const statusColor = result?.status === "Active"
    ? "bg-emerald-50 text-emerald-700 border-emerald-200"
    : result?.status === "Cancelled"
      ? "bg-red-50 text-red-700 border-red-200"
      : "bg-amber-50 text-amber-700 border-amber-200";

  return (
    <div className="min-h-screen bg-surface-raised">
      {/* Hero */}
      <div className="bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-white relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/20 blur-3xl" />
          <div className="absolute -bottom-32 -left-32 w-[500px] h-[500px] rounded-full bg-blue-300/20 blur-3xl" />
        </div>

        <div className="container mx-auto px-4 pt-12 pb-20 relative">
          <div className="text-center max-w-2xl mx-auto">
            <motion.div
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/15 rounded-full px-4 py-1.5 text-xs font-medium text-white/80 mb-5"
            >
              <Receipt className="w-3.5 h-3.5" />
              GST Verification Tool
            </motion.div>

            <motion.h1
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.05 }}
              className="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 tracking-tight"
            >
              GSTIN Lookup
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="text-white/60 text-base md:text-lg max-w-lg mx-auto mb-8"
            >
              Verify any GST Identification Number instantly. Get business name, registration status, address, and more.
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
                    placeholder="Enter GSTIN (e.g. 29AAGCR4375J1ZU)"
                    className="w-full bg-transparent py-4 pl-12 pr-36 text-white text-base md:text-lg font-mono tracking-widest placeholder:text-white/30 placeholder:tracking-normal placeholder:font-sans focus:outline-none"
                    autoFocus
                    spellCheck={false}
                    autoComplete="off"
                  />
                  <Button
                    type="submit"
                    size="lg"
                    disabled={loading || code.length < 15}
                    loading={loading}
                    className="absolute right-2 bg-white text-blue-700 hover:bg-white/90 hover:text-blue-800 shadow-lg shadow-black/10 font-bold"
                  >
                    {loading ? "Verifying..." : "Verify"}
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

              {code.length > 0 && !error && (
                <div className="mt-2 text-xs text-white/30 text-center font-mono">
                  {code.length}/15 characters
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
                  {[
                    { code: "29AAGCR4375J1ZU", label: "Razorpay" },
                    { code: "27AABCU9603R1ZM", label: "Uber India" },
                  ].map((s) => (
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
                    <div className="h-5 bg-gray-100 rounded w-2/5 mb-2" />
                    <div className="h-4 bg-gray-100 rounded w-1/4" />
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
                {/* Business header */}
                <div className="bg-gradient-to-r from-gray-50 to-white px-6 md:px-8 py-6 border-b border-border-light">
                  <div className="flex items-start gap-4">
                    <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20">
                      <Building2 className="w-7 h-7 text-white" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <h2 className="text-xl md:text-2xl font-extrabold text-text-primary leading-tight">
                        {result.trade_name || result.legal_name || "—"}
                      </h2>
                      {result.trade_name && result.legal_name && result.trade_name !== result.legal_name && (
                        <p className="text-sm text-text-muted mt-0.5">{result.legal_name}</p>
                      )}
                    </div>
                    <div className={cn("flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-bold border", statusColor)}>
                      {result.status || "Unknown"}
                    </div>
                  </div>
                </div>

                {/* GSTIN highlight strip */}
                <div className="px-6 md:px-8 py-4 bg-blue-50/50 border-b border-blue-100/60 flex items-center justify-between flex-wrap gap-3">
                  <div className="flex items-center gap-3">
                    <span className="text-xs font-semibold text-blue-600 uppercase tracking-wider">GSTIN</span>
                    <span className="font-mono text-xl md:text-2xl font-extrabold text-blue-700 tracking-[0.12em]">
                      {result.gstin}
                    </span>
                  </div>
                  <CopyButton text={result.gstin} label="GSTIN" copied={copied} onCopy={copyToClipboard} />
                </div>

                {/* Details grid */}
                <div className="px-6 md:px-8 py-6">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <DetailCard icon={<Briefcase className="w-4 h-4" />} label="Business Type" value={result.type} />
                    <DetailCard icon={<Scale className="w-4 h-4" />} label="Constitution" value={result.constitution} />
                    <DetailCard icon={<Calendar className="w-4 h-4" />} label="Registration Date" value={result.registration_date} />
                    <DetailCard icon={<MapPin className="w-4 h-4" />} label="State" value={stateName || result.address?.state || "—"} />
                    <DetailCard
                      icon={<Hash className="w-4 h-4" />}
                      label="PAN"
                      value={result.gstin ? result.gstin.slice(2, 12) : null}
                      copiable
                      copied={copied}
                      onCopy={copyToClipboard}
                    />
                    <DetailCard icon={<FileText className="w-4 h-4" />} label="E-Invoice" value={result.einvoice_status} />
                    {result.cancellation_date && (
                      <DetailCard icon={<AlertCircle className="w-4 h-4" />} label="Cancellation Date" value={result.cancellation_date} />
                    )}
                    {result.compliance_rating && result.compliance_rating !== "NA" && (
                      <DetailCard icon={<BadgeCheck className="w-4 h-4" />} label="Compliance Rating" value={result.compliance_rating} />
                    )}
                  </div>

                  {/* Nature of business */}
                  {result.nature_of_business && result.nature_of_business.length > 0 && (
                    <div className="mt-5">
                      <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">Nature of Business</p>
                      <div className="flex flex-wrap gap-2">
                        {result.nature_of_business.map((nba) => (
                          <span
                            key={nba}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200/60"
                          >
                            <CircleDot className="w-3 h-3" />
                            {nba}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* Address */}
                  {result.address?.full && (
                    <div className="mt-5 p-4 rounded-xl bg-gray-50 border border-border-light">
                      <div className="flex items-start gap-3">
                        <MapPin className="w-4 h-4 text-text-muted mt-0.5 flex-shrink-0" />
                        <div className="flex-1">
                          <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Principal Place of Business</p>
                          <p className="text-sm text-text-secondary leading-relaxed">{result.address.full}</p>
                          {result.address.pincode && (
                            <span className="inline-block mt-2 px-2 py-0.5 bg-white rounded-md text-xs font-mono font-semibold text-text-primary border border-border">
                              PIN: {result.address.pincode}
                            </span>
                          )}
                        </div>
                        <button
                          onClick={() => copyToClipboard(result.address!.full!, "Address")}
                          className="flex-shrink-0 p-1.5 rounded-lg hover:bg-gray-200 transition-colors"
                          title="Copy address"
                        >
                          {copied === "Address" ? <Check className="w-3.5 h-3.5 text-blue-600" /> : <Copy className="w-3.5 h-3.5 text-text-muted" />}
                        </button>
                      </div>
                    </div>
                  )}

                  {/* Jurisdictions */}
                  {(result.state_jurisdiction || result.central_jurisdiction) && (
                    <div className="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                      {result.state_jurisdiction && (
                        <div className="p-3.5 rounded-xl bg-gray-50/80 border border-border-light">
                          <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">State Jurisdiction</p>
                          <p className="text-xs text-text-secondary mt-1 leading-relaxed">{result.state_jurisdiction}</p>
                        </div>
                      )}
                      {result.central_jurisdiction && (
                        <div className="p-3.5 rounded-xl bg-gray-50/80 border border-border-light">
                          <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">Central Jurisdiction</p>
                          <p className="text-xs text-text-secondary mt-1 leading-relaxed">{result.central_jurisdiction}</p>
                        </div>
                      )}
                    </div>
                  )}
                </div>
              </div>

              {/* Search again */}
              <div className="text-center mt-6">
                <button
                  onClick={() => { setResult(null); setCode(""); inputRef.current?.focus(); }}
                  className="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors inline-flex items-center gap-1.5"
                >
                  <Search className="w-4 h-4" />
                  Verify another GSTIN
                </button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {/* Info section (no result) */}
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
                { icon: Zap, title: "Instant Verification", desc: "Verify GSTIN in real-time against government records with instant results." },
                { icon: ShieldCheck, title: "Prevent Fraud", desc: "Protect your business from fake GSTINs and ensure valid Input Tax Credit." },
                { icon: Globe, title: "Complete Details", desc: "Get business name, registration status, address, and compliance information." },
              ].map(({ icon: Icon, title, desc }, i) => (
                <motion.div
                  key={title}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.35 + i * 0.08 }}
                  className="bg-white rounded-2xl border border-border/60 p-6 shadow-sm hover:shadow-md transition-shadow"
                >
                  <div className="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                    <Icon className="w-5 h-5 text-blue-600" />
                  </div>
                  <h3 className="font-bold text-text-primary mb-1">{title}</h3>
                  <p className="text-sm text-text-muted leading-relaxed">{desc}</p>
                </motion.div>
              ))}
            </div>

            {/* What is GSTIN */}
            <div className="bg-white rounded-2xl border border-border/60 shadow-sm p-6 md:p-8">
              <h2 className="text-xl font-bold text-text-primary mb-4">What is a GSTIN?</h2>
              <div className="space-y-3 text-sm text-text-secondary leading-relaxed">
                <p>
                  <strong className="text-text-primary">GSTIN (Goods and Services Tax Identification Number)</strong> is
                  a unique 15-character alphanumeric code assigned to every registered taxpayer under GST in India.
                  It is essential for filing GST returns, claiming input tax credits, and conducting business transactions.
                </p>
                <p>
                  The number is structured as: first 2 digits represent the state code, next 10 characters are the
                  PAN of the entity, the 13th character is the entity number, the 14th is always
                  <strong className="text-text-primary"> Z</strong>, and the 15th is a check digit.
                </p>
              </div>

              {/* Visual breakdown */}
              <div className="mt-6 p-4 bg-gray-50 rounded-xl border border-border-light">
                <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">
                  GSTIN Structure
                </p>
                <div className="flex items-center gap-1 flex-wrap">
                  {["2", "9"].map((char, i) => (
                    <div key={`state-${i}`} className="w-9 h-11 rounded-lg bg-blue-100 border-2 border-blue-300 flex items-center justify-center font-mono text-base font-bold text-blue-700">
                      {char}
                    </div>
                  ))}
                  {["A", "A", "G", "C", "R", "4", "3", "7", "5", "J"].map((char, i) => (
                    <div key={`pan-${i}`} className="w-9 h-11 rounded-lg bg-emerald-100 border-2 border-emerald-300 flex items-center justify-center font-mono text-base font-bold text-emerald-700">
                      {char}
                    </div>
                  ))}
                  <div className="w-9 h-11 rounded-lg bg-amber-100 border-2 border-amber-300 flex items-center justify-center font-mono text-base font-bold text-amber-700">
                    1
                  </div>
                  <div className="w-9 h-11 rounded-lg bg-gray-200 border-2 border-gray-300 flex items-center justify-center font-mono text-base font-bold text-gray-500">
                    Z
                  </div>
                  <div className="w-9 h-11 rounded-lg bg-purple-100 border-2 border-purple-300 flex items-center justify-center font-mono text-base font-bold text-purple-700">
                    U
                  </div>
                </div>
                <div className="flex items-center gap-4 mt-3 text-xs font-medium flex-wrap">
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-blue-300" />
                    <span className="text-text-muted">State Code</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-emerald-300" />
                    <span className="text-text-muted">PAN Number</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-amber-300" />
                    <span className="text-text-muted">Entity No.</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-gray-300" />
                    <span className="text-text-muted">Default Z</span>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <div className="w-3 h-3 rounded bg-purple-300" />
                    <span className="text-text-muted">Check Digit</span>
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
  icon, label, value, copiable, copied, onCopy,
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
          {copied === label ? <Check className="w-3.5 h-3.5 text-blue-600" /> : <Copy className="w-3.5 h-3.5 text-text-muted" />}
        </button>
      )}
    </div>
  );
}

function CopyButton({
  text, label, copied, onCopy,
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
          ? "bg-blue-50 border-blue-200 text-blue-700"
          : "bg-white border-border text-text-secondary hover:border-blue-300 hover:text-blue-600"
      )}
    >
      {copied === label ? (
        <><Check className="w-3.5 h-3.5" /> Copied!</>
      ) : (
        <><Copy className="w-3.5 h-3.5" /> Copy GSTIN</>
      )}
    </button>
  );
}
