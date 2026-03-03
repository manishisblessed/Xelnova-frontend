# Xelnova Frontend – Verify & Push Guide

## 1. How to check IFSC & GSTIN integrations

### Prerequisites
- **Node.js** (for frontend)
- **PHP 8.3+ & Composer** (for GSTIN – API key is on backend)

### A. IFSC Lookup (`/ifsc`)
- **No backend required** – the page calls Razorpay directly.
- From project root:
  ```bash
  cd frontend
  npm install
  npm run dev
  ```
- Open **http://localhost:3000/ifsc**
- Enter an IFSC (e.g. `HDFC0CAGSBK` or use “Try: HDFC”) → click **Search**. You should see bank name, branch, address, NEFT/RTGS/IMPS/UPI.

### B. GSTIN Lookup (`/gstin`)
- **Backend required** – frontend calls your Laravel API (which uses the GSTIN API key).
- **Terminal 1 – Laravel:**
  ```bash
  # From project root (D:\tech\xelnova-web-app)
  composer install
  php artisan serve
  ```
- **Terminal 2 – Frontend:**
  ```bash
  cd frontend
  npm run dev
  ```
- In `frontend/.env.local` set:
  ```
  NEXT_PUBLIC_API_URL=http://localhost:8000
  ```
- In project root `.env` ensure:
  ```
  GSTIN_API_KEY=8a8e34cd78c26d2d0a59cc9a51746065
  ```
- Open **http://localhost:3000/gstin**
- Enter a GSTIN (e.g. `29AAGCR4375J1ZU` or use “Try: Razorpay”) → click **Verify**. You should see business name, status, address, jurisdictions.

### C. Seller bank account (IFSC auto-fill)
- Use the **Laravel** seller panel (e.g. `/seller/bank-accounts` when logged in as seller).
- Add/Edit bank account → enter an IFSC → tab out. Bank name and branch should auto-fill from the IFSC API.

---

## 2. Push frontend to GitHub (Xelnova-frontend)

Your repo: **https://github.com/manishisblessed/Xelnova-frontend.git**

Because the frontend lives inside a larger project, push only the `frontend` folder to this repo using one of the two methods below.

### Option A – Push only `frontend` (recommended)

Run from **project root** (`D:\tech\xelnova-web-app`):

```powershell
# Fix git "dubious ownership" if you see it (run once)
git config --global --add safe.directory D:/tech/xelnova-web-app

# Add the new remote (use a distinct name)
git remote add frontend-origin https://github.com/manishisblessed/Xelnova-frontend.git

# Push only the frontend folder to the main branch
git subtree push --prefix=frontend frontend-origin main
```

If the branch is `master` instead of `main`:

```powershell
git subtree push --prefix=frontend frontend-origin master
```

If the remote already has commits and you get errors, force-push the subtree once (only if you are sure the remote can be overwritten):

```powershell
git push frontend-origin $(git subtree split --prefix=frontend):main --force
```

### Option B – New repo with only frontend contents

Use this if you want a clean repo that contains only frontend files at the root (no `frontend/` folder inside the repo):

```powershell
# 1. Clone your empty repo into a new folder
cd D:\tech
git clone https://github.com/manishisblessed/Xelnova-frontend.git xelnova-frontend-temp
cd xelnova-frontend-temp

# 2. Copy everything from frontend into this repo (except node_modules and .next)
xcopy /E /I /EXCLUDE:D:\tech\xelnova-web-app\frontend\.gitignore D:\tech\xelnova-web-app\frontend\* .
# Or manually: copy all files from D:\tech\xelnova-web-app\frontend into D:\tech\xelnova-frontend-temp
# (excluding node_modules and .next)

# 3. Commit and push
git add .
git commit -m "Initial commit: Xelnova Next.js frontend with IFSC & GSTIN tools"
git push -u origin main
```

Then delete the temp folder if you don’t need it.

---

## 3. After pushing – clone and run

Anyone (or you on another machine) can get a working frontend like this:

```powershell
git clone https://github.com/manishisblessed/Xelnova-frontend.git
cd Xelnova-frontend
npm install
npm run dev
```

- **IFSC** will work at once (calls Razorpay from the browser).
- **GSTIN** will work only when the app can reach your Laravel backend. Set `NEXT_PUBLIC_API_URL` in `.env.local` to your Laravel URL (e.g. `http://localhost:8000` or your deployed API URL).

---

## 4. Common issues

| Issue | Cause | Fix |
|-------|--------|-----|
| **GET /xelnova-logo.png 404** | Logo file was missing. | The app now ships with `public/xelnova-logo.svg`. Replace it with your own `xelnova-logo.svg` or add `xelnova-logo.png` and point header/footer to it if you prefer PNG. |
| **GET /api/v1/cart/count 404** | Frontend calls the API on the same origin (Next.js) because `NEXT_PUBLIC_API_URL` is not set. | Set `NEXT_PUBLIC_API_URL=http://localhost:8000` in `frontend/.env.local` and run Laravel (`php artisan serve`) so cart and other API calls go to the backend. |
| **Upstream image response failed (Unsplash 404)** | Some demo Unsplash URLs may be removed or changed. | Broken demo images have been replaced with placeholders. For production, use your own product images or stable CDN URLs. |

---

## 5. Quick checklist

| Item | How to check |
|------|----------------------|
| IFSC page | `npm run dev` → open `/ifsc` → search HDFC0CAGSBK |
| GSTIN page | Laravel + frontend running, `.env` has GSTIN_API_KEY, frontend has NEXT_PUBLIC_API_URL → open `/gstin` → verify 29AAGCR4375J1ZU |
| Cart / API | Set NEXT_PUBLIC_API_URL and run Laravel; cart count and cart page will stop 404ing. |
| Footer links | Open any page → scroll to footer → “IFSC Code Lookup” and “GSTIN Lookup” open `/ifsc` and `/gstin` |
| Push to GitHub | Run Option A (subtree) or Option B (clean copy) from above |
