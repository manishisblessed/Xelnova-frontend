import { apiGet } from "./client";
import type { ApiResponse } from "./client";
import type { ApiListMeta } from "./client";
import type { ProductsListResponse, ApiProductListItem } from "./types";
import { apiProductToProduct } from "./types";
import type { Product } from "./types";

const BASE = "/api/v1";

export interface GetProductsParams {
  page?: number;
  per_page?: number;
  category?: string;
  brand?: string | number | (string | number)[];
  min_price?: number;
  max_price?: number;
  search?: string;
  sort?: "latest" | "price_low" | "price_high";
}

export interface GetProductsResult {
  products: Product[];
  meta: ApiListMeta;
}

export async function getProducts(params: GetProductsParams = {}): Promise<GetProductsResult> {
  const searchParams: Record<string, string | number> = {};
  if (params.page != null) searchParams.page = params.page;
  if (params.per_page != null) searchParams.per_page = params.per_page;
  if (params.category) searchParams.category = params.category;
  if (params.brand != null) {
    searchParams.brand = Array.isArray(params.brand) ? params.brand.join(",") : String(params.brand);
  }
  if (params.min_price != null) searchParams.min_price = params.min_price;
  if (params.max_price != null) searchParams.max_price = params.max_price;
  if (params.search) searchParams.search = params.search;
  if (params.sort) searchParams.sort = params.sort;

  type ProductsApiResponse = ApiResponse<ApiProductListItem[]> & { meta?: ApiListMeta };
  const res = await apiGet<ApiProductListItem[]>(`${BASE}/products`, searchParams as Record<string, string>) as ProductsApiResponse;

  const data = res.data ?? [];
  const meta: ApiListMeta = res.meta ?? {
    current_page: 1,
    last_page: 1,
    per_page: 24,
    total: 0,
    from: 0,
    to: 0,
    has_more: false,
  };

  const products = Array.isArray(data) ? data.map(apiProductToProduct) : [];
  return { products, meta };
}
