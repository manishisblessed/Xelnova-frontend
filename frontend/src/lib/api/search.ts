import { apiGet } from "./client";
import type { SearchAutocompleteData } from "./types";

const BASE = "/api/v1/search";

export interface SearchParams {
  q?: string;
  category?: string | number;
  brand?: string | number | (string | number)[];
  min_price?: number;
  max_price?: number;
  min_rating?: number;
  in_stock?: boolean;
  sort?: string;
  page?: number;
}

export async function searchAutocomplete(q: string): Promise<SearchAutocompleteData> {
  if (!q || q.length < 2) {
    return { products: [], categories: [], brands: [] };
  }
  const res = await apiGet<{ products: SearchAutocompleteData["products"]; categories: SearchAutocompleteData["categories"]; brands: SearchAutocompleteData["brands"] }>(
    `${BASE}/autocomplete`,
    { q }
  );
  if (!res.success || !res.data) return { products: [], categories: [], brands: [] };
  const d = res.data as unknown as SearchAutocompleteData;
  return {
    products: d.products ?? [],
    categories: d.categories ?? [],
    brands: d.brands ?? [],
  };
}

export async function search(params: SearchParams): Promise<{ data: unknown }> {
  const searchParams: Record<string, string | number | boolean> = {};
  if (params.q) searchParams.q = params.q;
  if (params.category != null) searchParams.category = String(params.category);
  if (params.brand != null) {
    searchParams.brand = Array.isArray(params.brand) ? params.brand.join(",") : String(params.brand);
  }
  if (params.min_price != null) searchParams.min_price = params.min_price;
  if (params.max_price != null) searchParams.max_price = params.max_price;
  if (params.min_rating != null) searchParams.min_rating = params.min_rating;
  if (params.in_stock != null) searchParams.in_stock = params.in_stock;
  if (params.sort) searchParams.sort = params.sort;
  if (params.page != null) searchParams.page = params.page;

  const res = await apiGet<unknown>(`${BASE}`, searchParams as Record<string, string>);
  return { data: res.data };
}

export async function popularSearches(): Promise<string[]> {
  const res = await apiGet<string[]>(`${BASE}/popular`);
  if (!res.success || !res.data) return [];
  return Array.isArray(res.data) ? res.data : [];
}
