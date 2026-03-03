import { apiGet } from "./client";
import type { DeliveryEstimateData } from "./types";

const BASE = "/api/v1/delivery";

export async function estimateDelivery(productId: number, pincode: string): Promise<DeliveryEstimateData> {
  const res = await apiGet<DeliveryEstimateData>(`${BASE}/estimate`, {
    product_id: productId,
    pincode: pincode.trim(),
  });
  if (!res.success || !res.data) {
    throw new Error((res as { message?: string }).message || "Failed to get delivery estimate");
  }
  return res.data;
}
