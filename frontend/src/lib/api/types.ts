import type { ApiListMeta } from "./client";

// --- Products API (v1/products) ---
export interface ApiProductListItem {
  id: number;
  variant_id: number | null;
  name: string;
  variant_label: string | null;
  slug: string;
  image: string;
  price: number;
  original_price: number | null;
  discount: number;
  rating: string;
  reviews_count: number;
  brand: string | null;
  category: string | null;
  delivery_text: string;
  in_stock: boolean;
}

/** Frontend Product shape used by ProductCard and pages */
export interface Product {
  id: number;
  name: string;
  slug: string;
  image: string;
  price: number;
  originalPrice?: number;
  rating?: number;
  reviewsCount?: number;
  brand?: string;
  freeDelivery?: boolean;
  variant_id?: number | null;
}

export function apiProductToProduct(item: ApiProductListItem): Product {
  return {
    id: item.id,
    name: item.name,
    slug: item.slug,
    image: item.image,
    price: item.price,
    originalPrice: item.original_price ?? undefined,
    rating: typeof item.rating === "string" ? parseFloat(item.rating) : item.rating,
    reviewsCount: item.reviews_count,
    brand: item.brand ?? undefined,
    freeDelivery: item.delivery_text?.toLowerCase().includes("free") ?? false,
    variant_id: item.variant_id,
  };
}

export interface ProductsListResponse {
  data: ApiProductListItem[];
  meta: ApiListMeta;
}

// --- Cart API ---
export interface ApiCartItem {
  id: number;
  product_id: number;
  product_slug: string;
  variant_id: number | null;
  variant_label: string | null;
  name: string;
  image: string;
  price: number;
  original_price: number | null;
  discount: number;
  quantity: number;
  total: number;
  in_stock: boolean;
  stock_quantity: number;
  max_quantity: number;
}

export interface ApiCartCoupon {
  code: string;
  name: string;
  discount: number;
}

export interface ApiCartSummary {
  items: ApiCartItem[];
  count: number;
  products_count: number;
  subtotal: number;
  discount: number;
  shipping_charge: number;
  tax: number;
  total: number;
  savings: number;
  coupon: ApiCartCoupon | null;
}

/** Cart item for UI (compatible with existing cart page) */
export interface CartItemDisplay extends Product {
  quantity: number;
  itemId: number;
}

// --- Search API ---
export interface SearchAutocompleteProduct {
  id: number;
  name: string;
  price: string;
  image: string | null;
  url: string;
}

export interface SearchAutocompleteCategory {
  id: number;
  name: string;
  url: string;
}

export interface SearchAutocompleteBrand {
  id: number;
  name: string;
  url: string;
}

export interface SearchAutocompleteData {
  products: SearchAutocompleteProduct[];
  categories: SearchAutocompleteCategory[];
  brands: SearchAutocompleteBrand[];
}

// --- Delivery API ---
export interface DeliveryEstimateData {
  delivery_date: string;
  message: string;
  distance_km?: number;
}

// --- Wishlist (for future use when auth is wired) ---
export interface WishlistCheckResponse {
  in_wishlist: boolean;
}

// --- Reviews (for future use) ---
export interface ApiReview {
  id: number;
  product_id: number;
  user_id: number;
  rating: number;
  title: string | null;
  comment: string;
  is_verified_purchase: boolean;
  is_approved: boolean;
  created_at: string;
  user?: { name: string };
}
