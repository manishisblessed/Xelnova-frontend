import { apiGet } from "./client";

const BASE = "/api/v1/gstin";

export interface GstinAddress {
  full: string | null;
  building_name: string | null;
  building_no: string | null;
  floor_no: string | null;
  street: string | null;
  location: string | null;
  district: string | null;
  state: string | null;
  pincode: string | null;
  city: string | null;
}

export interface GstinDetails {
  gstin: string;
  legal_name: string | null;
  trade_name: string | null;
  status: string | null;
  type: string | null;
  constitution: string | null;
  registration_date: string | null;
  cancellation_date: string | null;
  einvoice_status: string | null;
  state_jurisdiction: string | null;
  central_jurisdiction: string | null;
  nature_of_business: string[];
  compliance_rating: string | null;
  address: GstinAddress | null;
}

/**
 * Look up GSTIN via our backend (which proxies to gstincheck.co.in).
 */
export async function lookupGstin(gstin: string): Promise<GstinDetails | null> {
  const normalized = gstin.trim().toUpperCase().replace(/\s+/g, "");
  if (normalized.length !== 15) return null;
  try {
    const res = await apiGet<GstinDetails>(`${BASE}/${normalized}`);
    if (res.success && res.data) return res.data;
  } catch {
    // 404, 422, or network error
  }
  return null;
}

/**
 * Validate GSTIN format (offline, no API credit used).
 */
export async function validateGstinFormat(gstin: string): Promise<boolean> {
  const normalized = gstin.trim().toUpperCase().replace(/\s+/g, "");
  if (normalized.length !== 15) return false;
  try {
    const res = await apiGet<unknown>(`${BASE}/validate`, { gstin: normalized }) as { valid?: boolean };
    return res.valid === true;
  } catch {
    return false;
  }
}

/**
 * Client-side GSTIN format check (no network call).
 */
export function isValidGstinFormat(gstin: string): boolean {
  return /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(
    gstin.trim().toUpperCase().replace(/\s+/g, "")
  );
}
