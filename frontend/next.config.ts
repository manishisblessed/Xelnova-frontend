import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  env: {
    GSTIN_API_KEY: process.env.GSTIN_API_KEY ?? "",
  },
  images: {
    dangerouslyAllowSVG: true,
    contentDispositionType: "attachment",
    remotePatterns: [
      { protocol: "https", hostname: "placehold.co" },
      { protocol: "https", hostname: "images.unsplash.com" },
      { protocol: "https", hostname: "picsum.photos" },
    ],
  },
};

export default nextConfig;
