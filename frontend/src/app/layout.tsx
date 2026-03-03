import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { Header } from "@/components/layout/header";
import { Footer } from "@/components/layout/footer";
import { Toaster } from "sonner";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-sans",
  display: "swap",
});

export const metadata: Metadata = {
  title: "Xelnova — Online Shopping for Electronics, Fashion, Home & More",
  description:
    "Your one-stop marketplace for electronics, fashion, home & more. Best offers on top brands with fast delivery.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className={inter.variable}>
      <body className="font-sans antialiased min-h-screen flex flex-col">
        <Header />
        <main className="flex-grow">{children}</main>
        <Footer />
        <Toaster
          position="top-right"
          toastOptions={{
            className: "!rounded-xl !shadow-xl !border !border-border",
          }}
        />
      </body>
    </html>
  );
}
