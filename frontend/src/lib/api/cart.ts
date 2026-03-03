import { apiGet, apiPost, apiPut, apiDelete } from "./client";
import type { ApiCartSummary, ApiCartItem } from "./types";

const BASE = "/api/v1/cart";

/** Cart line item for UI (id is product_id, itemId is cart line id) */
export interface CartItem {
  id: number;
  name: string;
  slug: string;
  image: string;
  price: number;
  originalPrice?: number;
  quantity: number;
  itemId: number;
}

export interface CartSummary {
  items: CartItem[];
  count: number;
  subtotal: number;
  discount: number;
  shippingCharge: number;
  total: number;
  coupon: { code: string; name: string; discount: number } | null;
}

function mapCartResponse(data: ApiCartSummary): CartSummary {
  return {
    items: (data.items || []).map((item) => ({
      id: item.product_id,
      name: item.name,
      slug: item.product_slug,
      image: item.image,
      price: item.price,
      originalPrice: item.original_price ?? undefined,
      quantity: item.quantity,
      itemId: item.id,
    })),
    count: data.products_count ?? data.count ?? 0,
    subtotal: data.subtotal ?? 0,
    discount: data.discount ?? 0,
    shippingCharge: data.shipping_charge ?? 0,
    total: data.total ?? 0,
    coupon: data.coupon ?? null,
  };
}

export async function getCart(): Promise<CartSummary> {
  const res = await apiGet<ApiCartSummary>(BASE);
  if (!res.success || !res.data) {
    return {
      items: [],
      count: 0,
      subtotal: 0,
      discount: 0,
      shippingCharge: 0,
      total: 0,
      coupon: null,
    };
  }
  return mapCartResponse(res.data);
}

export async function addToCart(productId: number, quantity: number, variantId?: number | null): Promise<CartSummary> {
  const res = await apiPost<ApiCartSummary>(`${BASE}/add`, {
    product_id: productId,
    quantity,
    variant_id: variantId ?? undefined,
  });
  if (!res.success || !res.data) throw new Error(res.message || "Failed to add to cart");
  return mapCartResponse(res.data);
}

export async function updateCartItem(itemId: number, quantity: number): Promise<CartSummary> {
  const res = await apiPut<ApiCartSummary>(`${BASE}/item/${itemId}`, { quantity });
  if (!res.success || !res.data) throw new Error(res.message || "Failed to update cart");
  return mapCartResponse(res.data);
}

export async function removeCartItem(itemId: number): Promise<CartSummary> {
  const res = await apiDelete<ApiCartSummary>(`${BASE}/item/${itemId}`);
  if (!res.success || !res.data) throw new Error(res.message || "Failed to remove item");
  return mapCartResponse(res.data);
}

export async function clearCart(): Promise<CartSummary> {
  const res = await apiDelete<ApiCartSummary>(`${BASE}/clear`);
  if (!res.success || !res.data) throw new Error(res.message || "Failed to clear cart");
  return mapCartResponse(res.data);
}

export async function applyCoupon(code: string): Promise<CartSummary> {
  const res = await apiPost<ApiCartSummary & { message?: string }>(`${BASE}/coupon`, { coupon_code: code });
  if (!res.success || !res.data) throw new Error((res as { message?: string }).message || "Failed to apply coupon");
  return mapCartResponse(res.data);
}

export async function removeCoupon(): Promise<CartSummary> {
  const res = await apiDelete<ApiCartSummary>(`${BASE}/coupon`);
  if (!res.success || !res.data) throw new Error(res.message || "Failed to remove coupon");
  return mapCartResponse(res.data);
}

export async function getCartCount(): Promise<number> {
  try {
    const res = await apiGet<unknown>(`${BASE}/count`) as { success?: boolean; count?: number };
    if (res.success && typeof res.count === "number") return res.count;
  } catch {
    // ignore
  }
  return 0;
}
