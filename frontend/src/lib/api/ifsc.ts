import { apiGet } from "./client";

const BASE = "/api/v1/ifsc";

export interface IfscDetails {
  ifsc: string;
  bank: string;
  branch: string;
  address: string | null;
  city: string | null;
  district: string | null;
  state: string | null;
  contact: string | null;
  bankcode: string | null;
  micr: string | null;
  rtgs: boolean;
  neft: boolean;
  imps: boolean;
  upi: boolean;
}

/**
 * Look up IFSC code via Razorpay IFSC API (proxied through our backend).
 * Returns bank name, branch, address, etc. for auto-filling forms.
 */
export async function lookupIfsc(code: string): Promise<IfscDetails | null> {
  const normalized = code.trim().toUpperCase().replace(/\s+/g, "");
  if (normalized.length !== 11) return null;
  try {
    const res = await apiGet<IfscDetails>(`${BASE}/${normalized}`);
    if (res.success && res.data) return res.data;
  } catch {
    // 404 or network error
  }
  return null;
}

/**
 * Validate IFSC code (offline check via backend).
 */
export async function validateIfsc(code: string): Promise<boolean> {
  const normalized = code.trim().toUpperCase().replace(/\s+/g, "");
  if (normalized.length !== 11) return false;
  try {
    const res = await apiGet<unknown>(`${BASE}/validate`, { code: normalized }) as { valid?: boolean };
    return res.valid === true;
  } catch {
    return false;
  }
}
