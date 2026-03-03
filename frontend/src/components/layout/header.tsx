"use client";

import { useState, useRef, useEffect, useCallback } from "react";
import Link from "next/link";
import Image from "next/image";
import { useRouter } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  Search,
  ShoppingCart,
  User,
  Menu,
  X,
  ChevronDown,
  ChevronRight,
  Phone,
  Heart,
  Package,
  LogOut,
  MapPin,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { NAV_CATEGORIES, SUPPORT_PHONE } from "@/lib/constants";
import { getCartCount } from "@/lib/api/cart";
import { searchAutocomplete } from "@/lib/api/search";
import type { SearchAutocompleteData } from "@/lib/api/types";

interface Category {
  id: number;
  name: string;
  slug: string;
  children?: Category[];
}

export function Header() {
  const router = useRouter();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [searchFocused, setSearchFocused] = useState(false);
  const [searchResults, setSearchResults] = useState<SearchAutocompleteData | null>(null);
  const [searchLoading, setSearchLoading] = useState(false);
  const [activeDropdown, setActiveDropdown] = useState<number | null>(null);
  const [activeSubcategory, setActiveSubcategory] = useState<number | null>(null);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const [cartCount, setCartCount] = useState(0);
  const searchRef = useRef<HTMLDivElement>(null);
  const userMenuRef = useRef<HTMLDivElement>(null);
  const isLoggedIn = false;

  useEffect(() => {
    getCartCount().then(setCartCount);
    const onCartUpdated = () => getCartCount().then(setCartCount);
    window.addEventListener("cart-updated", onCartUpdated);
    return () => window.removeEventListener("cart-updated", onCartUpdated);
  }, []);

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (searchRef.current && !searchRef.current.contains(e.target as Node)) {
        setSearchFocused(false);
      }
      if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) {
        setUserMenuOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleSearch = useCallback(
    (e: React.FormEvent) => {
      e.preventDefault();
      if (searchQuery.trim()) {
        router.push(`/products?search=${encodeURIComponent(searchQuery.trim())}`);
        setSearchFocused(false);
      }
    },
    [searchQuery, router]
  );

  useEffect(() => {
    if (!searchQuery || searchQuery.length < 2) {
      setSearchResults(null);
      return;
    }
    const t = setTimeout(() => {
      setSearchLoading(true);
      searchAutocomplete(searchQuery)
        .then(setSearchResults)
        .finally(() => setSearchLoading(false));
    }, 200);
    return () => clearTimeout(t);
  }, [searchQuery]);

  return (
    <header
      className={cn(
        "sticky top-0 z-50 transition-all duration-300",
        isScrolled ? "glass shadow-lg shadow-black/[0.03]" : "bg-white"
      )}
    >
      {/* Top Bar */}
      <div className="bg-gradient-to-r from-primary-800 via-primary-700 to-primary-800 text-white/90 text-xs">
        <div className="container mx-auto px-4 flex justify-between items-center h-8">
          <div className="flex items-center gap-4">
            <span className="flex items-center gap-1.5">
              <Phone className="w-3 h-3" />
              Support: {SUPPORT_PHONE}
            </span>
            <span className="hidden sm:inline text-white/40">|</span>
            <span className="hidden sm:inline hover:text-white cursor-pointer transition-colors">
              Download App
            </span>
          </div>
          <div className="flex items-center gap-4">
            <Link
              href="/seller"
              className="hover:text-accent-300 transition-colors font-medium"
            >
              Sell on Xelnova
            </Link>
            <span className="text-white/40">|</span>
            <Link href="/track-order" className="flex items-center gap-1 hover:text-accent-300 transition-colors">
              <MapPin className="w-3 h-3" />
              Track Order
            </Link>
          </div>
        </div>
      </div>

      {/* Main Header */}
      <div className="container mx-auto px-4 h-16 flex items-center justify-between gap-4">
        {/* Logo */}
        <Link href="/" className="flex-shrink-0 group flex items-center">
          <Image
            src="/xelnova-logo.png"
            alt="Xelnova"
            width={140}
            height={40}
            className="h-9 w-auto object-contain object-left group-hover:opacity-90 transition-opacity"
            priority
          />
        </Link>

        {/* Search Bar */}
        <div
          ref={searchRef}
          className="hidden md:flex flex-1 max-w-2xl mx-6 relative"
        >
          <form onSubmit={handleSearch} className="w-full">
            <div
              className={cn(
                "relative flex items-center rounded-2xl border transition-all duration-300",
                searchFocused
                  ? "border-primary-500 ring-4 ring-primary-500/10 bg-white shadow-lg"
                  : "border-gray-200 bg-gray-50/80 hover:border-gray-300 hover:bg-gray-50"
              )}
            >
              <Search className="absolute left-4 w-4.5 h-4.5 text-text-muted" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onFocus={() => setSearchFocused(true)}
                placeholder="Search for products, brands and more..."
                className="w-full bg-transparent py-2.5 pl-11 pr-24 text-sm focus:outline-none placeholder:text-text-muted"
              />
              <button
                type="submit"
                className="absolute right-1.5 bg-primary-600 hover:bg-primary-700 text-white px-5 py-1.5 rounded-xl text-sm font-semibold transition-all duration-200 active:scale-95"
              >
                Search
              </button>
            </div>
          </form>
          {/* Search autocomplete dropdown */}
          <AnimatePresence>
            {searchFocused && (searchResults || searchLoading) && (
              <motion.div
                initial={{ opacity: 0, y: -4 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -4 }}
                className="absolute top-full left-0 right-0 mt-1 bg-white rounded-2xl border border-border shadow-xl shadow-black/10 overflow-hidden z-50 max-h-80 overflow-y-auto"
              >
                {searchLoading ? (
                  <div className="p-4 text-center text-text-muted text-sm">Searching...</div>
                ) : searchResults && (searchResults.products.length > 0 || searchResults.categories.length > 0 || searchResults.brands.length > 0) ? (
                  <div className="p-2">
                    {searchResults.products.length > 0 && (
                      <div className="mb-2">
                        <p className="text-xs font-semibold text-text-muted uppercase px-2 py-1">Products</p>
                        {searchResults.products.slice(0, 5).map((p) => (
                          <a
                            key={p.id}
                            href={p.url}
                            className="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 text-left"
                          >
                            {p.image && <img src={p.image} alt="" className="w-10 h-10 rounded-lg object-cover" />}
                            <div className="min-w-0 flex-1">
                              <p className="text-sm font-medium text-text-primary truncate">{p.name}</p>
                              <p className="text-xs text-text-muted">{p.price}</p>
                            </div>
                          </a>
                        ))}
                      </div>
                    )}
                    {(searchResults.categories.length > 0 || searchResults.brands.length > 0) && (
                      <div className="border-t border-border pt-2">
                        {searchResults.categories.length > 0 && (
                          <div className="mb-1">
                            <p className="text-xs font-semibold text-text-muted uppercase px-2 py-1">Categories</p>
                            {searchResults.categories.map((c) => (
                              <a key={c.id} href={c.url} className="block px-3 py-2 rounded-xl hover:bg-gray-50 text-sm text-text-secondary">{c.name}</a>
                            ))}
                          </div>
                        )}
                        {searchResults.brands.length > 0 && (
                          <div>
                            <p className="text-xs font-semibold text-text-muted uppercase px-2 py-1">Brands</p>
                            {searchResults.brands.map((b) => (
                              <a key={b.id} href={b.url} className="block px-3 py-2 rounded-xl hover:bg-gray-50 text-sm text-text-secondary">{b.name}</a>
                            ))}
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                ) : searchQuery.length >= 2 ? (
                  <div className="p-4 text-center text-text-muted text-sm">No results. Try different keywords.</div>
                ) : null}
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {/* Actions */}
        <div className="flex items-center gap-1">
          {/* User */}
          {isLoggedIn ? (
            <div ref={userMenuRef} className="relative hidden sm:block">
              <button
                onClick={() => setUserMenuOpen(!userMenuOpen)}
                className="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 group"
              >
                <div className="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                  <User className="w-4 h-4 text-primary-700" />
                </div>
                <span className="text-sm font-medium text-text-primary group-hover:text-primary-700 hidden lg:block">
                  Account
                </span>
                <ChevronDown className="w-3.5 h-3.5 text-text-muted hidden lg:block" />
              </button>
              <AnimatePresence>
                {userMenuOpen && (
                  <motion.div
                    initial={{ opacity: 0, y: 8, scale: 0.96 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, y: 8, scale: 0.96 }}
                    transition={{ duration: 0.15, ease: [0.16, 1, 0.3, 1] }}
                    className="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl shadow-black/10 border border-border overflow-hidden"
                  >
                    <div className="p-2">
                      <Link
                        href="/account/orders"
                        className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-text-secondary hover:bg-gray-50 hover:text-text-primary transition-colors"
                      >
                        <Package className="w-4 h-4" /> My Orders
                      </Link>
                      <Link
                        href="/account/wishlist"
                        className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-text-secondary hover:bg-gray-50 hover:text-text-primary transition-colors"
                      >
                        <Heart className="w-4 h-4" /> My Wishlist
                      </Link>
                      <Link
                        href="/account/profile"
                        className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-text-secondary hover:bg-gray-50 hover:text-text-primary transition-colors"
                      >
                        <User className="w-4 h-4" /> My Profile
                      </Link>
                      <div className="my-1 border-t border-border" />
                      <button className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-600 hover:bg-red-50 transition-colors w-full">
                        <LogOut className="w-4 h-4" /> Logout
                      </button>
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          ) : (
            <Link
              href="/login"
              className="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 group"
            >
              <div className="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-primary-100 flex items-center justify-center transition-colors">
                <User className="w-4 h-4 text-text-muted group-hover:text-primary-700 transition-colors" />
              </div>
              <span className="text-sm font-medium text-text-primary hidden lg:block">Login</span>
            </Link>
          )}

          {/* Wishlist */}
          <Link
            href="/account/wishlist"
            className="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 group"
          >
            <Heart className="w-5 h-5 text-text-muted group-hover:text-red-500 transition-colors" />
          </Link>

          {/* Cart */}
          <Link
            href="/cart"
            className="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 group relative"
          >
            <div className="relative">
              <ShoppingCart className="w-5 h-5 text-text-muted group-hover:text-primary-700 transition-colors" />
              {cartCount > 0 && (
                <motion.span
                  initial={{ scale: 0 }}
                  animate={{ scale: 1 }}
                  className="absolute -top-1.5 -right-2 bg-accent-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center rounded-full shadow-sm"
                >
                  {cartCount > 9 ? "9+" : cartCount}
                </motion.span>
              )}
            </div>
            <span className="text-sm font-medium text-text-primary hidden lg:block">Cart</span>
          </Link>

          {/* Mobile Menu Toggle */}
          <button
            onClick={() => setMobileMenuOpen(true)}
            className="md:hidden p-2 rounded-xl hover:bg-gray-100 transition-colors"
          >
            <Menu className="w-5 h-5 text-text-secondary" />
          </button>
        </div>
      </div>

      {/* Mobile Search */}
      <div className="md:hidden px-4 pb-3">
        <form onSubmit={handleSearch} className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search products..."
            className="w-full bg-gray-100 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all"
          />
        </form>
      </div>

      {/* Category Navigation - Desktop */}
      <nav className="hidden md:block border-t border-border-light">
        <div className="container mx-auto px-4">
          <ul className="flex items-center gap-0 text-sm">
            {NAV_CATEGORIES.map((category) => (
              <li
                key={category.id}
                className="relative"
                onMouseEnter={() => {
                  setActiveDropdown(category.id);
                  const firstChild = category.children?.[0];
                  setActiveSubcategory(firstChild?.id ?? null);
                }}
                onMouseLeave={() => {
                  setActiveDropdown(null);
                  setActiveSubcategory(null);
                }}
              >
                <Link
                  href={`/products?category=${category.slug}`}
                  className={cn(
                    "flex items-center gap-1 px-4 py-3 font-medium transition-all duration-200 relative",
                    activeDropdown === category.id
                      ? "text-primary-700"
                      : "text-text-secondary hover:text-text-primary"
                  )}
                >
                  {category.name}
                  {category.children && category.children.length > 0 && (
                    <ChevronDown
                      className={cn(
                        "w-3.5 h-3.5 transition-transform duration-200",
                        activeDropdown === category.id && "rotate-180"
                      )}
                    />
                  )}
                  {activeDropdown === category.id && (
                    <motion.div
                      layoutId="category-underline"
                      className="absolute bottom-0 left-4 right-4 h-0.5 bg-primary-600 rounded-full"
                    />
                  )}
                </Link>

                {/* Dropdown */}
                <AnimatePresence>
                  {activeDropdown === category.id && category.children && category.children.length > 0 && (
                    <motion.div
                      initial={{ opacity: 0, y: -4 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -4 }}
                      transition={{ duration: 0.2, ease: [0.16, 1, 0.3, 1] }}
                      className="absolute top-full left-0 bg-white shadow-2xl shadow-black/8 border border-border rounded-2xl overflow-hidden z-50"
                      style={{ minWidth: 560 }}
                    >
                      <div className="flex">
                        {/* Level 2 */}
                        <div className="w-56 bg-gray-50/80 border-r border-border py-3">
                          {category.children.map((sub: Category) => (
                            <Link
                              key={sub.id}
                              href={`/products?category=${sub.slug}`}
                              onMouseEnter={() => setActiveSubcategory(sub.id)}
                              className={cn(
                                "flex items-center justify-between px-5 py-2.5 text-sm transition-all duration-150",
                                activeSubcategory === sub.id
                                  ? "bg-white text-primary-700 font-semibold border-l-[3px] border-primary-600 shadow-sm"
                                  : "text-text-secondary hover:text-text-primary hover:bg-white/50 border-l-[3px] border-transparent"
                              )}
                            >
                              {sub.name}
                              {sub.children && sub.children.length > 0 && (
                                <ChevronRight className="w-3.5 h-3.5 opacity-40" />
                              )}
                            </Link>
                          ))}
                        </div>
                        {/* Level 3 */}
                        <div className="flex-1 p-5 min-w-[280px]">
                          {category.children.map((sub: Category) => (
                            <div
                              key={sub.id}
                              className={cn(
                                "transition-opacity duration-150",
                                activeSubcategory === sub.id ? "block" : "hidden"
                              )}
                            >
                              {sub.children && sub.children.length > 0 ? (
                                <>
                                  <h4 className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">
                                    {sub.name}
                                  </h4>
                                  <div className="space-y-0.5">
                                    {sub.children.map((child: Category) => (
                                      <Link
                                        key={child.id}
                                        href={`/products?category=${child.slug}`}
                                        className="block text-sm text-text-secondary hover:text-primary-700 py-2 px-3 rounded-lg hover:bg-primary-50 transition-all duration-150"
                                      >
                                        {child.name}
                                      </Link>
                                    ))}
                                  </div>
                                </>
                              ) : (
                                <Link
                                  href={`/products?category=${sub.slug}`}
                                  className="text-primary-600 hover:underline text-sm"
                                >
                                  View all {sub.name}
                                </Link>
                              )}
                            </div>
                          ))}
                        </div>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </li>
            ))}
          </ul>
        </div>
      </nav>

      {/* Mobile Menu Overlay */}
      <AnimatePresence>
        {mobileMenuOpen && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setMobileMenuOpen(false)}
              className="fixed inset-0 bg-black/40 z-50 md:hidden"
            />
            <motion.div
              initial={{ x: "-100%" }}
              animate={{ x: 0 }}
              exit={{ x: "-100%" }}
              transition={{ type: "spring", damping: 30, stiffness: 300 }}
              className="fixed inset-y-0 left-0 w-[85%] max-w-sm bg-white z-50 md:hidden flex flex-col shadow-2xl"
            >
              {/* Mobile Header */}
              <div className="bg-gradient-to-r from-primary-600 to-primary-700 p-5 flex justify-between items-center">
                <div className="flex items-center gap-3 text-white">
                  <div className="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <User className="w-5 h-5" />
                  </div>
                  <div>
                    <p className="font-semibold text-lg">Hello!</p>
                    <p className="text-sm text-white/80">Login or Sign up</p>
                  </div>
                </div>
                <button
                  onClick={() => setMobileMenuOpen(false)}
                  className="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition text-white"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              {/* Mobile Nav Links */}
              <div className="flex-1 overflow-y-auto py-4">
                {[
                  { name: "All Categories", href: "/products", icon: Menu },
                  { name: "My Orders", href: "/account/orders", icon: Package },
                  { name: "My Cart", href: "/cart", icon: ShoppingCart },
                  { name: "My Wishlist", href: "/account/wishlist", icon: Heart },
                  { name: "My Account", href: "/account/profile", icon: User },
                ].map((item, i) => (
                  <motion.div
                    key={item.name}
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.05 }}
                  >
                    <Link
                      href={item.href}
                      onClick={() => setMobileMenuOpen(false)}
                      className="flex items-center gap-4 px-6 py-3.5 text-text-primary hover:bg-gray-50 transition-colors"
                    >
                      <item.icon className="w-5 h-5 text-text-muted" />
                      <span className="font-medium">{item.name}</span>
                    </Link>
                  </motion.div>
                ))}
                <div className="my-3 mx-6 border-t border-border" />
                <motion.div
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.3 }}
                >
                  <Link
                    href="/contact"
                    onClick={() => setMobileMenuOpen(false)}
                    className="flex items-center gap-4 px-6 py-3.5 text-text-primary hover:bg-gray-50 transition-colors"
                  >
                    <Phone className="w-5 h-5 text-text-muted" />
                    <span className="font-medium">Help Centre</span>
                  </Link>
                </motion.div>
              </div>

              {/* Mobile Footer */}
              <div className="p-4 border-t border-border bg-gray-50">
                <Link
                  href="/seller"
                  className="block text-center py-2.5 bg-accent-500 text-white rounded-xl font-semibold hover:bg-accent-600 transition-colors"
                >
                  Sell on Xelnova
                </Link>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>
    </header>
  );
}
