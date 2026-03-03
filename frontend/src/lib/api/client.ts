/**
 * Base URL for the Laravel API (no trailing slash).
 * Set NEXT_PUBLIC_API_URL in .env.local (e.g. http://localhost:8000 for Laravel).
 */
export function getApiBaseUrl(): string {
  if (typeof window !== "undefined") {
    return process.env.NEXT_PUBLIC_API_URL ?? "";
  }
  return process.env.NEXT_PUBLIC_API_URL ?? "";
}

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public body?: unknown
  ) {
    super(message);
    this.name = "ApiError";
  }
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
}

export interface ApiListMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  has_more: boolean;
}

async function parseResponse<T>(res: Response): Promise<ApiResponse<T>> {
  const text = await res.text();
  let json: ApiResponse<T>;
  try {
    json = text ? JSON.parse(text) : {};
  } catch {
    throw new ApiError(res.statusText || "Invalid JSON", res.status, text);
  }
  return json;
}

export async function apiRequest<T>(
  path: string,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  const base = getApiBaseUrl().replace(/\/$/, "");
  const url = path.startsWith("http") ? path : `${base}${path.startsWith("/") ? path : `/${path}`}`;

  const headers: HeadersInit = {
    Accept: "application/json",
    "Content-Type": "application/json",
    ...options.headers,
  };

  const res = await fetch(url, {
    ...options,
    headers,
    credentials: "include",
  });

  const data = await parseResponse<T>(res);

  if (!res.ok) {
    throw new ApiError(
      data.message || data.error || res.statusText || "Request failed",
      res.status,
      data
    );
  }

  return data;
}

export async function apiGet<T>(path: string, params?: Record<string, string | number | undefined>): Promise<ApiResponse<T>> {
  const search = params
    ? new URLSearchParams(
        Object.entries(params).filter(([, v]) => v !== undefined && v !== "") as [string, string][]
      ).toString()
    : "";
  const url = search ? `${path}?${search}` : path;
  return apiRequest<T>(url, { method: "GET" });
}

export async function apiPost<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { method: "POST", body: body ? JSON.stringify(body) : undefined });
}

export async function apiPut<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { method: "PUT", body: body ? JSON.stringify(body) : undefined });
}

export async function apiDelete<T>(path: string): Promise<ApiResponse<T>> {
  return apiRequest<T>(path, { method: "DELETE" });
}
