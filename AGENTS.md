# Product Overview

This is a comprehensive **Multivendor B2C Ecommerce Platform** that allows multiple independent sellers to list and sell products to end customers under one unified marketplace, similar to Amazon. Customers can browse, search, purchase, return, and review products, while sellers manage their catalogs, orders, and payouts through a dedicated portal.

## Project Goals

- Create a user-friendly, secure, and scalable online marketplace
- Allow multiple sellers to onboard, manage products, and fulfill orders independently
- Provide a seamless buying experience with unified checkout, payments, and delivery
- Enable administrators to monitor and control all marketplace activities

## Key Stakeholders

### Customers (Buyers)
- Browse, purchase, and review products
- Track orders and manage returns

### Sellers (Vendors)
- Register and verify identity
- Manage product catalog
- Fulfill orders and handle returns
- View earnings and payouts

### Marketplace Admins
- Approve sellers and manage products
- Monitor orders and handle disputes
- Configure fees and promotions

### Support Staff
- Handle customer issues and refunds
- Manage disputes and complaints

## Core Functional Modules

### 4.1 Customer Features

#### Registration & Login
- Simple registration with email or phone
- OTP-based verification

#### Product Search & Browse
- Category-based browsing with filters
- Filter by price, brand, rating, and availability

#### Product Details Page
- Comprehensive product information with images
- Reviews, offers, and seller options

#### Shopping Cart
- Add products from multiple sellers in a single cart

#### Checkout
- Address management
- Shipping method selection
- Coupon usage and secure payment

#### Order Tracking
- Real-time status updates for processing, shipping, and delivery

#### Returns & Refunds
- Simple return initiation with tracking for refunds or replacements

#### Reviews & Ratings
- Verified customers can rate and review purchased items

### 4.2 Seller (Vendor) Features

#### Registration & KYC
- Seller onboarding with business and bank verification

#### Product Management
- Add, edit, or delete products
- Upload images and descriptions

#### Stock & Pricing Control
- Manage inventory
- Set prices or discounts

#### Order Management
- View new orders
- Update statuses (packed, shipped, delivered)
- Print invoices and shipping labels

#### Returns & Refunds
- Handle return requests and update statuses

#### Finance Dashboard
- View commissions, fees, and payout summaries

### 4.3 Admin Features

#### User & Seller Management
- Approve or suspend sellers
- Manage user accounts

#### Catalog Oversight
- Review and approve products before publication

#### Order Monitoring
- View all orders
- Manage disputes
- Issue manual refunds if needed

#### Commission & Payouts
- Configure marketplace fees
- Manage periodic settlements to sellers

#### Content Management
- Manage homepage banners and promotional sections
- Manage static pages (FAQ, Contact, etc.)

#### Reports & Analytics
- Sales and revenue reports
- Top products analysis
- Seller performance metrics
- Returns overview

### 4.4 Order & Payment Flow
- Customers can buy products from multiple sellers in one checkout
- The system automatically splits the order among sellers while processing one payment
- Each seller can independently pack and ship their part of the order
- Admin manages payouts to sellers after successful delivery

### 4.5 Shipping & Logistics
- Integration with leading courier partners for label generation, pickup, and tracking
- Delivery charges configurable per seller or order value
- Returns pickup and delivery tracking

### 4.6 Promotions & Marketing
- Discount coupons and promotional codes
- Flash deals, featured products, and seasonal campaigns
- Email and SMS notifications for offers and order updates

### 4.7 Customer Support
- In-platform messaging or ticket-based support
- Status tracking for customer queries and complaints
- Return and refund assistance

## System Highlights (Functional View)

- **Multi-Vendor Marketplace**: Each seller has their own dashboard and inventory but operates under a common storefront
- **Unified Checkout**: Customers can place a single order even when buying from multiple sellers
- **Payment Gateway Integration**: Secure payment with cards, UPI, wallets, and net banking
- **Refund & Settlement Handling**: Refunds to buyers and periodic settlements to sellers
- **Notifications**: Automatic alerts for order, shipping, returns, and promotions
- **Reports**: Comprehensive performance and sales insights for admin and sellers

## Non-Functional Requirements

- **Scalability**: Designed to handle large product catalogs and user traffic
- **Security**: Secure transactions, encrypted data, and role-based access
- **Performance**: Optimized for fast product search and checkout flow
- **Responsiveness**: Fully mobile-friendly user interface
- **Compliance**: Follows applicable ecommerce and data protection guidelines

## Deliverables

- Web application for customers (storefront)
- Seller portal for vendor operations
- Admin dashboard for management and reporting
- Integrated payment and logistics workflows
- Reports for sales, commissions, and payouts
- Documentation for system operation and support

## User Roles

- **Super User** - Full system control
- **Admin** - User and permission management, reports, marketplace configuration
- **Seller** - Product and order management, payout tracking
- **Customer** - Browse, purchase, review products
- **Support Staff** - Customer service and dispute handling

## Currency and Localization

- **Currency**: Indian Rupee (₹) - All prices, payments, and financial transactions use INR
- **Currency Symbol**: ₹ (Unicode: U+20B9)
- **Number Format**: Indian numbering system (e.g., ₹1,00,000 for one lakh)
- **Decimal Places**: 2 decimal places for all monetary values
- **Usage**: Always use the ₹ symbol when displaying prices in the UI and reports
- **Database Storage**: Store prices as `decimal(10,2)` in the database


# Project Structure

## Root Directory Layout

```
├── app/                    # Laravel application code
├── bootstrap/              # Laravel bootstrap files
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── packages/               # Custom/modified packages
├── public/                 # Web server document root
├── resources/              # Views, assets, language files
├── routes/                 # Route definitions
├── storage/                # Logs, cache, uploads
├── tests/                  # Test files
└── vendor/                 # Composer dependencies
```

## Application Structure (`app/`)

### Core Directories

- **Console/** - Artisan commands and kernel
- **Exceptions/** - Exception handling
- **Http/** - Controllers, middleware, requests
- **Models/** - Eloquent models
- **Providers/** - Service providers
- **Rules/** - Custom validation rules
- **Services/** - Business logic services
- **Traits/** - Reusable traits
- **Enums/** - PHP enums for constants
- **Helpers/** - Helper classes and functions
- **Mail/** - Mail classes
- **Notifications/** - Notification classes

### HTTP Layer (`app/Http/`)

```
Http/
├── Controllers/
│   ├── Admin/              # Admin panel controllers
│   ├── Seller/             # Seller portal controllers
│   ├── Customer/           # Customer-facing controllers
│   └── Api/                # API controllers
├── Middleware/             # Custom middleware
└── Requests/               # Form request validation
```

### Models Organization (`app/Models/`)

Models are organized by business domain:

- **User Management**: User, Role, Permission, SigninLog, UserEmailCode, SellerProfile
- **Catalog**: Category, SubCategory, Product, ProductVariant, ProductImage, Brand
- **Order Management**: Order, OrderItem, OrderSplit, Shipment, ShipmentTracking
- **Payment**: Payment, PaymentTransaction, Refund, SellerPayout
- **Seller**: Seller, SellerDocument, SellerBankAccount, Commission
- **Customer**: Cart, CartItem, Wishlist, Address, Review, Rating
- **Promotions**: Coupon, CouponUsage, FlashDeal, FeaturedProduct
- **Support**: Ticket, TicketMessage, Dispute
- **System**: Setting, LogActivity, Notification

## Frontend Structure (`resources/`)

### Blade Views (`resources/views/`)

**Actual current structure:**

```
views/
├── marketplace/                    # Customer storefront (Blade + Alpine)
│   ├── index.blade.php            # Homepage
│   ├── products.blade.php         # Product listing
│   ├── product-detail.blade.php   # Product detail page
│   ├── cart.blade.php             # Shopping cart
│   ├── checkout.blade.php         # Checkout flow
│   ├── account/                   # Customer account pages
│   │   ├── my-orders.blade.php
│   │   ├── order-tracking.blade.php
│   │   ├── profile.blade.php
│   │   └── wishlist.blade.php
│   ├── auth/                      # Customer auth pages
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   └── pages/                     # Static pages
│       ├── about.blade.php
│       ├── contact.blade.php
│       ├── faq.blade.php
│       ├── privacy.blade.php
│       ├── returns.blade.php
│       └── terms.blade.php
├── seller/                         # Seller portal (Blade + Alpine)
│   ├── dashboard.blade.php
│   ├── auth/
│   │   └── register.blade.php
│   ├── products/
│   │   ├── index.blade.php
│   │   └── form.blade.php
│   ├── orders/
│   │   └── index.blade.php
│   └── finance/
│       └── index.blade.php
├── components/                     # Shared Blade components
│   ├── marketplace/               # Storefront components
│   │   ├── layout.blade.php       # Main storefront layout
│   │   ├── header.blade.php
│   │   ├── footer.blade.php
│   │   ├── product-card.blade.php
│   │   └── category-card.blade.php
│   ├── seller/                    # Seller portal components
│   │   └── layout.blade.php
│   ├── admin/                     # Admin Blade components
│   └── ui/                        # Generic UI components
├── Admin/                          # Admin Blade views (if any)
│   └── marketplace/               # Marketplace admin pages
├── layouts/                        # Base layouts
└── emails/                         # Email templates
```

### Vue.js Pages (`resources/js/Pages/Admin/`)

**Admin panel uses Inertia + Vue.js:**

```
Pages/Admin/
├── IndexView.vue              # Reusable list/table view
├── AddEditView.vue            # Reusable form view
├── MultiAddEditView.vue       # Bulk operations view
├── DashboardView.vue          # Admin dashboard
├── UserIndexView.vue          # User list (custom)
├── UserPermissionsEditView.vue # User permissions editor
├── ProfileView.vue            # User profile
├── SettingIndexView.vue       # Settings list
├── SettingAddEditView.vue     # Settings form
├── SettingShowView.vue        # Settings detail
├── ImportView.vue             # Data import
├── BarcodePrint.vue           # Barcode printing
└── Scan.vue                   # Barcode scanning
```

### JavaScript (`resources/js/`)

```
js/
├── Pages/                      # Inertia.js Vue pages
│   ├── Admin/                  # Admin panel pages
│   └── Auth/                   # Auth pages
├── components/                 # Reusable Vue components
├── layouts/                    # Vue layout components
├── stores/                     # Pinia stores
├── helpers/                    # JavaScript utilities
├── app.js                      # Main Vue/Inertia entry
├── marketplace.js              # Marketplace Alpine.js entry
├── bootstrap.js                # Bootstrap configuration
├── menuAside.js                # Admin sidebar menu config
└── menuNavBar.js               # Admin navbar config
```

### CSS (`resources/css/`)

```
css/
├── admin.css                   # Admin panel styles (Vue/Inertia)
├── frontend.css                # Marketplace + Seller styles (Blade/Alpine)
├── _checkbox-radio-switch.css  # Admin form components
├── _progress.css               # Progress bar styles
├── _scrollbars.css             # Custom scrollbars
├── _table.css                  # Table styles
├── vue-multiselect.css         # Vue multiselect component
└── tailwind/                   # Tailwind CSS customizations
```

**CSS Separation:**
- `admin.css` - Used by `app.js` for Admin panel (Vue/Inertia)
- `frontend.css` - Used by `marketplace.js` for Storefront and Seller Portal (Blade/Alpine)

### Component Usage Patterns

**Marketplace (Blade):**
```blade
<x-marketplace.layout>
    @section('title', 'Page Title')
    <!-- Page content -->
</x-marketplace.layout>
```

**Seller Portal (Blade):**
```blade
<x-seller.layout>
    <!-- Seller dashboard content -->
</x-seller.layout>
```

**Admin (Vue/Inertia):**
```php
// In Controller
return Inertia::render('Admin/UserIndexView', [
    'users' => $users,
    'resourceNeo' => $this->resourceNeo
]);
```

## Database Structure (`database/`)

### Key Tables

- **users** - All user types with role-based access
- **sellers** - Seller profiles and verification status
- **categories** - Product categories hierarchy
- **products** - Product catalog with seller reference
- **product_variants** - Product variations (size, color, etc.)
- **orders** - Customer orders
- **order_items** - Individual items in orders
- **order_splits** - Seller-wise order splits
- **payments** - Payment records
- **seller_payouts** - Payout settlements to sellers
- **reviews** - Customer reviews and ratings
- **carts** - Shopping cart data
- **addresses** - Customer addresses
- **coupons** - Promotional coupons


## Naming Conventions

### Controllers

- **Location**: `app/Http/Controllers/Admin/`, `app/Http/Controllers/Seller/`, `app/Http/Controllers/Customer/`
- **Pattern**: `{Entity}Controller.php`
- **Example**: `ProductController.php`, `OrderController.php`

### Models

- **Location**: `app/Models/`
- **Pattern**: Singular PascalCase
- **Example**: `Product.php`, `OrderItem.php`

### Blade Views

- **Location**: `resources/views/admin/`, `resources/views/seller/`, `resources/views/customer/`
- **Pattern**: `{entity}/{action}.blade.php`
- **Examples**:
    - `products/index.blade.php` (listing)
    - `products/create.blade.php` / `products/edit.blade.php` (forms)
    - `orders/show.blade.php` (details)
    - `cart/checkout.blade.php` (checkout flow)

### Migrations

- **Pattern**: `YYYY_MM_DD_HHMMSS_descriptive_action.php`
- **Example**: `2025_12_12_120000_create_products_table.php`


## Current Routes Structure (`routes/web.php`)

### Public Marketplace Routes

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/` | GET | `Customer\StorefrontController@home` | Homepage |
| `/products` | GET | `Customer\StorefrontController@products` | Product listing |
| `/product/{id}` | GET | `Customer\StorefrontController@productDetail` | Product detail |
| `/cart` | GET | `Customer\StorefrontController@cart` | Shopping cart |
| `/checkout` | GET | `Customer\StorefrontController@checkout` | Checkout page |

### Customer Auth Routes

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/customer/login` | GET | `Customer\StorefrontController@login` | Customer login |
| `/customer/register` | GET | `Customer\StorefrontController@register` | Customer registration |

### Customer Account Routes (prefix: `/account`)

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/account/orders` | GET | `Customer\StorefrontController@myOrders` | Order history |
| `/account/track-order` | GET | `Customer\StorefrontController@orderTracking` | Order tracking |
| `/account/profile` | GET | `Customer\StorefrontController@profile` | Profile settings |
| `/account/wishlist` | GET | `Customer\StorefrontController@wishlist` | Wishlist |

### Seller Portal Routes (prefix: `/seller`)

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/seller/register` | GET | `Seller\SellerDashboardController@register` | Seller registration |
| `/seller/dashboard` | GET | `Seller\SellerDashboardController@dashboard` | Seller dashboard |
| `/seller/products` | GET | `Seller\SellerDashboardController@products` | Product list |
| `/seller/products/create` | GET | `Seller\SellerDashboardController@createProduct` | Add product |
| `/seller/orders` | GET | `Seller\SellerDashboardController@orders` | Order management |
| `/seller/finance` | GET | `Seller\SellerDashboardController@finance` | Financial dashboard |

### Admin Routes (prefix: `/admin`, middleware: `auth`, `2fa`)

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/admin/dashboard` | GET | `Admin\DashboardController@index` | Admin dashboard |
| `/admin/user` | Resource | `Admin\UserController` | User CRUD |
| `/admin/role` | Resource | `Admin\RoleController` | Role CRUD |
| `/admin/permission` | Resource | `Admin\PermissionController` | Permission CRUD |
| `/admin/activityLog` | Resource | `Admin\LogActivityController` | Activity logs |
| `/admin/signinLog` | Resource | `Admin\SigninlogController` | Login logs |
| `/admin/setting` | Resource | `Admin\SettingController` | Settings |

### Static Pages Routes

| Route | Method | Purpose |
|-------|--------|---------|
| `/about-us` | GET | About page |
| `/contact-us` | GET | Contact page |
| `/faq` | GET | FAQ page |
| `/terms-conditions` | GET | Terms page |
| `/privacy-policy` | GET | Privacy page |
| `/return-policy` | GET | Returns page |

### API Routes (`routes/api.php`)

| Route | Method | Controller | Purpose |
|-------|--------|------------|---------|
| `/api/v1/cart` | GET | `Api\CartController@index` | Get cart contents |
| `/api/v1/cart/add` | POST | `Api\CartController@add` | Add item to cart |
| `/api/v1/cart/item/{id}` | PUT | `Api\CartController@update` | Update item quantity |
| `/api/v1/cart/item/{id}` | DELETE | `Api\CartController@remove` | Remove item |
| `/api/v1/cart/clear` | DELETE | `Api\CartController@clear` | Clear cart |
| `/api/v1/cart/coupon` | POST | `Api\CartController@applyCoupon` | Apply coupon |
| `/api/v1/cart/coupon` | DELETE | `Api\CartController@removeCoupon` | Remove coupon |

## New Module Creation Checklist

When creating a new module, the following files **MUST** be updated:

### 1. Database & Backend

- [ ] **Migration**: Create migration file in `database/migrations/`
- [ ] **Model**: Create model in `app/Models/`
- [ ] **Controller**: Create controller in `app/Http/Controllers/Admin/` (or Seller/Customer)
- [ ] **Routes**: Add routes in `routes/web.php`
- [ ] **Form Request**: Create validation request in `app/Http/Requests/` (if needed)

### 2. Permissions (Required)

- [ ] **`database/seeders/UnifiedPermissionSeeder.php`**: Add permissions for the new module
  ```php
  // Example: Add to the permissions array
  'moduleName_view',
  'moduleName_create',
  'moduleName_edit',
  'moduleName_delete',
  ```
- Run `php artisan db:seed --class=UnifiedPermissionSeeder` after updating

### 3. Configuration (Required)

- [ ] **`config/app.php`**: Add module to the `'modules'` array for permission grouping:
  ```php
  'modules' => [
      // ... existing modules
      'newModule' => 'New Module Display Name',
  ],
  ```
  **This is required for the user permissions UI to properly group permissions.**

### 4. Frontend Menu (Required)

- [ ] **`resources/js/menuAside.js`**: Add menu item for the new module
  ```javascript
  // Example: Add to the appropriate section
  {
    to: '/admin/module-name',
    label: 'Module Name',
    icon: mdiIconName,
    permission: 'moduleName_view',
  },
  ```

### 5. Views

**For Admin modules (Inertia + Vue):**
- [ ] **Vue Pages**: Create/use pages in `resources/js/Pages/Admin/`
  - Use existing `IndexView.vue` for list views (preferred)
  - Use existing `AddEditView.vue` for forms (preferred)
  - Create custom `{Module}IndexView.vue` if needed

**For Marketplace/Seller modules (Blade):**
- [ ] **Blade Views**: Create views in `resources/views/{marketplace|seller}/{module}/`
  - `index.blade.php` - List view
  - `create.blade.php` - Create form
  - `edit.blade.php` - Edit form
  - `show.blade.php` - Detail view (if needed)

### Quick Reference: Files to Update for New Module

| Step | File/Location | Purpose |
|------|---------------|---------|
| 1 | `database/migrations/` | Database schema |
| 2 | `app/Models/` | Eloquent model |
| 3 | `app/Http/Controllers/` | Business logic |
| 4 | `routes/web.php` | URL routing |
| 5 | `database/seeders/UnifiedPermissionSeeder.php` | **Permissions** |
| 6 | `config/app.php` → `'modules'` array | **Module registry for permissions UI** |
| 7 | `resources/js/menuAside.js` | **Sidebar menu** |
| 8 | `resources/js/Pages/Admin/` (Vue) OR `resources/views/` (Blade) | Views |


# Existing Modules & Controllers

## Current Admin Controllers (`app/Http/Controllers/Admin/`)

| Controller | Purpose | Permissions |
|------------|---------|-------------|
| `UserController.php` | User CRUD, profile, permissions | `user_list`, `user_create`, `user_edit`, `user_delete` |
| `RoleController.php` | Role management | `role_list`, `role_create`, `role_edit`, `role_delete` |
| `PermissionController.php` | Permission management | `permission_list`, `permission_create`, `permission_edit`, `permission_delete` |
| `SigninlogController.php` | Login activity logs | `signinLog_list`, `signinLog_delete`, `signinLog_view` |
| `LogActivityController.php` | Activity audit logs | `activityLog_list`, `activityLog_delete` |
| `SettingController.php` | System settings | Settings management |
| `DashboardController.php` | Admin dashboard | General access |
| `TwoFAController.php` | Two-factor authentication | Auth related |

## Customer Controllers (`app/Http/Controllers/Customer/`)

| Controller | Purpose |
|------------|---------|
| `StorefrontController.php` | Marketplace pages (home, products, cart, checkout, account, static pages) |

## Seller Controllers (`app/Http/Controllers/Seller/`)

| Controller | Purpose |
|------------|---------|
| `SellerDashboardController.php` | Seller portal (dashboard, products, orders, finance) |

## API Controllers (`app/Http/Controllers/Api/`)

| Controller | Purpose |
|------------|---------|
| `CartController.php` | Real-time cart operations (add, update, remove, coupon) |

## Other Controllers (`app/Http/Controllers/`)

| Controller | Purpose |
|------------|---------|
| `MarketplaceAdminController.php` | Marketplace admin Blade pages |

## Current Models (`app/Models/`)

| Model | Purpose |
|-------|--------|
| `User.php` | User accounts with roles/permissions |
| `Role.php` | User roles (Spatie) |
| `Permission.php` | Permissions (Spatie) |
| `SigninLog.php` | Login tracking |
| `LogActivity.php` | Action audit logs |
| `Setting.php` | System settings |
| `UserEmailCode.php` | Email verification codes |

## Controller Pattern: resourceNeo

Admin controllers use a `$resourceNeo` property for standardized UI configuration:

```php
protected $resourceNeo = [
    'resourceName' => 'user',           // Used for permission checks
    'resourceTitle' => 'Users',          // Display title
    'iconPath' => 'M4,6H2V20...'         // MDI icon path
];
```

## Controller Pattern: Permission Middleware

Controllers should define permission middleware in constructor:

```php
public function __construct()
{
    $this->middleware('can:moduleName_list', ['only' => ['index', 'show']]);
    $this->middleware('can:moduleName_create', ['only' => ['create', 'store']]);
    $this->middleware('can:moduleName_edit', ['only' => ['edit', 'update']]);
    $this->middleware('can:moduleName_delete', ['only' => ['destroy']]);
}
```

## Controller Pattern: N+1 Query Prevention (CRITICAL)

**N+1 queries are a critical performance issue.** Always eager load relationships to prevent multiple database queries when accessing related data.

### What is N+1?

When you load a list of models and then access a relationship on each model in a loop, Laravel makes 1 query for the main models + N queries for each relationship access = N+1 queries.

**Bad Example (N+1 Problem):**
```php
// This causes N+1 queries!
$products = Product::all();
foreach ($products as $product) {
    echo $product->category->name;  // Each access triggers a new query
}
```

**Good Example (Eager Loading):**
```php
// This uses only 2 queries total
$products = Product::with('category')->get();
foreach ($products as $product) {
    echo $product->category->name;  // Already loaded, no new query
}
```

### Common N+1 Scenarios & Solutions

#### 1. User Roles (Spatie Permission)

The `User` model has a `role_name` appended attribute that accesses roles. Always eager load `roles`:

```php
// BAD
$users = User::all();

// GOOD
$users = User::with('roles')->get();

// For sellers with users
$sellers = Seller::with('user.roles')->get();
```

#### 2. Category Parent (Hierarchical Data)

The `Category` model has a `full_path` accessor that traverses parent categories. Load nested parents:

```php
// BAD - causes N+1 for each parent level
$categories = Category::all();

// GOOD - load up to 3 levels of parents
$categories = Category::with('parent.parent.parent')->get();

// For products with categories
$products = Product::with([
    'category' => function ($query) {
        $query->with('parent.parent.parent');
    },
    'brand'
])->get();
```

#### 3. Multiple Relationships

When loading multiple relationships, use array syntax:

```php
// Load multiple relationships
$sellers = Seller::with(['user.roles', 'documents', 'bankAccounts', 'approvedBy'])->get();

// For products
$products = Product::with(['category.parent', 'brand', 'seller', 'images'])->get();
```

### Quick Reference: Common Eager Loading Patterns

| Scenario | Eager Load Pattern |
|----------|-------------------|
| User with roles | `User::with('roles')` |
| Seller with user | `Seller::with('user.roles')` |
| Category with parent | `Category::with('parent.parent.parent')` |
| Product with category | `Product::with(['category.parent.parent', 'brand'])` |
| Order with items | `Order::with(['items.product', 'customer', 'seller'])` |

### Checklist for Every Controller Method

Before returning data from a controller method, ask yourself:

- [ ] **Am I loading a collection?** If yes, check for N+1
- [ ] **What relationships are accessed in the view/frontend?** Eager load them
- [ ] **Are there any model accessors (like `full_path`, `role_name`)?** These often trigger hidden queries
- [ ] **Does the relationship have nested relationships?** Load them with dot notation (e.g., `user.roles`)
- [ ] **Use Laravel Debugbar** to verify query count during development

### Detection Tools

1. **Laravel Debugbar**: Shows all queries on each page load
2. **Laravel Telescope**: Records all queries for analysis
3. **`DB::enableQueryLog()`**: Manually log queries for debugging

```php
// Debug queries manually
DB::enableQueryLog();
$products = Product::with('category')->get();
dd(DB::getQueryLog()); // See all queries executed
```

## Controller Pattern: InertiaTable for Admin Index Views

Admin controllers use `InertiaTable` from `protone-media/laravel-query-builder-inertia-js` to define table columns and filters. This provides a standardized way to create data tables with sorting, filtering, and pagination.

### Required Import

```php
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
```

### Basic Pattern

```php
public function index()
{
    // 1. Build query with QueryBuilder
    $query = Model::with(['relationships']);
    
    $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
        $query->where(function ($query) use ($value) {
            $query->where('field1', 'LIKE', "%{$value}%")
                  ->orWhere('field2', 'LIKE', "%{$value}%");
        });
    });
    
    $perPage = request()->query('perPage') ?? 10;
    
    $data = QueryBuilder::for($query)
        ->defaultSort('-created_at')
        ->allowedSorts(['field1', 'field2', 'created_at'])
        ->allowedFilters(['field1', 'field2', $globalSearch])
        ->paginate($perPage)
        ->withQueryString();
    
    // 2. Return Inertia with table definition
    return Inertia::render('Admin/IndexView', [
        'resourceData' => $data,  // MUST be named 'resourceData'
        'resourceNeo' => $this->resourceNeo,
    ])->table(function (InertiaTable $table) {
        $table->withGlobalSearch()
            ->column('field1', 'Label 1', searchable: true, sortable: true)
            ->column('field2', 'Label 2', searchable: true, sortable: true)
            ->column('field3', 'Label 3', searchable: false, sortable: false)
            ->column(label: 'Actions')  // Actions column (no key needed)
            ->perPageOptions([10, 15, 30, 50, 100])
            ->selectFilter(key: 'status', label: 'Status', options: [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ])
            ->dateFilter(key: 'created_start', label: 'Date From')
            ->dateFilter(key: 'created_end', label: 'Date To');
    });
}
```

### Column Definition

```php
->column(
    key: 'field_name',      // Database column name
    label: 'Display Name',  // Column header
    searchable: true,       // Enable search for this column
    sortable: true          // Enable sorting for this column
)
```

**Special Columns:**
- **Actions Column**: `->column(label: 'Actions')` - No key needed, used for edit/delete buttons
- **Custom Keys**: Use different display key: `->column('formatted_date', 'Date', sortable: true)` where `formatted_date` is an accessor

### Filter Types

**Select Filter (Dropdown):**
```php
->selectFilter(key: 'status', label: 'Status', options: [
    'pending' => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
])
```

**Date Filter:**
```php
->dateFilter(key: 'created_start', label: 'Date From')
->dateFilter(key: 'created_end', label: 'Date To')
```

**Important**: Filters must match the `allowedFilters` in QueryBuilder. For date filters, you may need to add scopes to your model:

```php
// In Model
public function scopeCreatedStart($query, $value)
{
    $query->whereDate('created_at', '>=', $value);
}

public function scopeCreatedEnd($query, $value)
{
    $query->whereDate('created_at', '<=', $value);
}

// In Controller allowedFilters
->allowedFilters([
    AllowedFilter::scope('created_start'),
    AllowedFilter::scope('created_end'),
])
```

### Complete Example (SellerManagementController)

```php
public function index()
{
    $query = Seller::with(['user', 'approvedBy']);
    
    $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
        $query->where(function ($query) use ($value) {
            $query->where('business_name', 'LIKE', "%{$value}%")
                  ->orWhere('email', 'LIKE', "%{$value}%")
                  ->orWhere('phone', 'LIKE', "%{$value}%");
        });
    });
    
    $perPage = request()->query('perPage') ?? 10;
    $sellers = QueryBuilder::for($query)
        ->defaultSort('-created_at')
        ->allowedSorts(['business_name', 'status', 'created_at'])
        ->allowedFilters(['status', 'verification_status', 'business_type', $globalSearch])
        ->paginate($perPage)
        ->withQueryString();
    
    return Inertia::render('Admin/IndexView', [
        'resourceData' => $sellers,
        'resourceNeo' => $this->resourceNeo,
    ])->table(function (InertiaTable $table) {
        $table->withGlobalSearch()
            ->column('business_name', 'Business Name', searchable: true, sortable: true)
            ->column('email', 'Email', searchable: true, sortable: true)
            ->column('phone', 'Phone', searchable: false, sortable: false)
            ->column('status', 'Status', searchable: false, sortable: true)
            ->column('verification_status', 'Verification', searchable: false, sortable: false)
            ->column('created_at', 'Created Date', searchable: false, sortable: true)
            ->column(label: 'Actions')
            ->perPageOptions([10, 15, 30, 50, 100])
            ->selectFilter(key: 'status', label: 'Status', options: [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'suspended' => 'Suspended',
                'rejected' => 'Rejected',
            ])
            ->selectFilter(key: 'verification_status', label: 'Verification', options: [
                'unverified' => 'Unverified',
                'verified' => 'Verified',
                'rejected' => 'Rejected',
            ])
            ->selectFilter(key: 'business_type', label: 'Business Type', options: [
                'individual' => 'Individual',
                'company' => 'Company',
                'partnership' => 'Partnership',
            ]);
    });
}
```

### Key Points

1. **Data Prop Name**: Must be `resourceData` (not `users`, `sellers`, etc.)
2. **Global Search**: Define callback filter and add to `allowedFilters`
3. **Filters**: Use `->selectFilter()` and `->dateFilter()` methods, not separate array
4. **Sorting**: Columns marked `sortable: true` must be in `allowedSorts`
5. **Actions Column**: Always include `->column(label: 'Actions')` for edit/delete buttons
6. **Pagination**: Use `->perPageOptions()` to define available page sizes

## Controller Pattern: CRUD Module Convention

**IMPORTANT**: This is the standard pattern for creating CRUD modules. Follow this convention strictly.

### Overview

CRUD modules follow a specific pattern where:
1. **`formInfo()` is defined in the MODEL** as a static method
2. **Validation rules** are extracted from `formInfo` in the controller
3. **No separate FormRequest classes** - validation is done inline
4. **Dynamic table columns** are built from `formInfo` metadata

### Step 1: Define formInfo() in Model

The model must have a static `formInfo()` method that returns field configurations:

```php
// app/Models/Brand.php
class Brand extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    
    /**
     * Get form information for CRUD operations.
     */
    public static function formInfo()
    {
        return [
            'name' => [
                'label' => 'Brand Name',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255|unique:brands,name',
                'tooltip' => 'Enter the brand name',
                'searchable' => true,
                'sortable' => true,
            ],
            'slug' => [
                'label' => 'URL Slug',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255|alpha_dash|unique:brands,slug',
                'tooltip' => 'URL-friendly version',
                'searchable' => true,
                'sortable' => true,
            ],
            'description' => [
                'label' => 'Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string',
                'tooltip' => 'Brief description',
                'searchable' => false,
                'sortable' => false,
            ],
            'is_active' => [
                'label' => 'Active Status',
                'type' => 'switch',
                'default' => true,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Is this active?',
                'searchable' => false,
                'sortable' => true,
            ],
            'logo' => [
                'label' => 'Logo',
                'type' => 'file',
                'default' => '',
                'vRule' => 'nullable|image|max:2048',
                'tooltip' => 'Upload logo (max 2MB)',
                'searchable' => false,
                'sortable' => false,
            ],
        ];
    }
}
```

### formInfo Field Properties

| Property | Required | Description |
|----------|----------|-------------|
| `label` | Yes | Display label for the field |
| `type` | Yes | Field type: `input`, `textarea`, `select`, `switch`, `file`, `datepicker`, `datetimepicker`, `password`, `number` |
| `default` | Yes | Default value for new records |
| `vRule` | No | Laravel validation rules (string format) |
| `tooltip` | No | Help text shown below field |
| `searchable` | No | Whether field is searchable in table (default: false) |
| `sortable` | No | Whether field is sortable in table (default: false) |
| `hidden` | No | Whether to hide column in table (default: false) |
| `options` | No | For `select` type: array of options |

### Step 2: Controller Structure

```php
// app/Http/Controllers/Admin/BrandController.php
class BrandController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'brand',
        'resourceTitle' => 'Brands',
        'iconPath' => 'M5.5,9A1.5...',  // MDI icon path
        'actions' => ['c', 'r', 'u', 'd']
    ];

    public function __construct()
    {
        $this->middleware('can:brand_list', ['only' => ['index', 'show']]);
        $this->middleware('can:brand_create', ['only' => ['create', 'store']]);
        $this->middleware('can:brand_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:brand_delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formInfo = Brand::formInfo();
        $formInfoMulti = [];  // For multi-select or relationship fields
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) use ($formInfo, $formInfoMulti) {
            $query->where(function ($query) use ($value, $formInfo, $formInfoMulti) {
                Collection::wrap($value)->each(function ($value) use ($query, $formInfo, $formInfoMulti) {
                    foreach (array_keys($formInfo) as $key) {
                        $query->orWhere($key, 'LIKE', "%{$value}%");
                    }
                    foreach (array_keys($formInfoMulti) as $key) {
                        $query->orWhere($key, 'LIKE', "%{$value}%");
                    }
                });
            });
        });

        $perPage = request()->query('perPage') ?? 10;
        $resourceData = QueryBuilder::for(Brand::class)
            ->defaultSort('name')
            ->allowedSorts(array_merge(array_keys($formInfo), array_keys($formInfoMulti), []))
            ->allowedFilters(array_merge(array_keys($formInfo), array_keys($formInfoMulti), [$globalSearch]))
            ->paginate($perPage)
            ->withQueryString();

        // Add bulk actions if user has permission
        if (Auth::user()->can('brand_delete')) {
            $this->resourceNeo['bulkActions'] = ['bulk_delete' => []];
        }
        if (Auth::user()->can('brand_export')) {
            $this->resourceNeo['bulkActions']['csvExport'] = [];
        }

        return Inertia::render('Admin/IndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo
        ])->table(function (InertiaTable $table) use ($formInfo, $formInfoMulti) {
            $table->withGlobalSearch();
            
            // Add columns from formInfo (exclude fields you don't want in table)
            $arrKey = array_diff(array_keys($formInfo), ['logo', 'description']);
            foreach ($arrKey as $key) {
                $table->column(
                    $key,
                    $formInfo[$key]['label'],
                    searchable: $formInfo[$key]['searchable'] ?? false,
                    sortable: $formInfo[$key]['sortable'] ?? false,
                    hidden: $formInfo[$key]['hidden'] ?? false
                );
            }
            
            // Add columns from formInfoMulti
            foreach (array_keys($formInfoMulti) as $key) {
                $table->column(
                    $key,
                    $formInfoMulti[$key]['label'],
                    searchable: $formInfoMulti[$key]['searchable'] ?? false,
                    sortable: $formInfoMulti[$key]['sortable'] ?? false,
                    hidden: $formInfoMulti[$key]['hidden'] ?? false
                );
            }
            
            $table
                ->column(label: 'Actions')
                ->perPageOptions([10, 15, 30, 50, 100]);
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = Brand::formInfo();
        return Inertia::render('Admin/AddEditView', compact('resourceNeo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $formInfo = Brand::formInfo();
        $attributeNames = [];
        $validateRule = [];
        $savedArray = [];
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
            
            // Handle file upload separately
            if ($key === 'logo' && $request->hasFile('logo')) {
                $savedArray[$key] = $request->file('logo')->store('brands/logos', 'public');
            } else {
                $savedArray[$key] = $request->{$key};
            }
        }

        $request->validate($validateRule, [], $attributeNames);
        Brand::create($savedArray);

        \ActivityLog::add([
            'action' => 'created',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $request->{array_keys($formInfo)[0]}
        ]);

        return redirect()->route('brand.index')->with([
            'message' => 'Brand Created Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        $formdata = $brand;
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = Brand::formInfo();
        return Inertia::render('Admin/AddEditView', compact('formdata', 'resourceNeo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $formInfo = Brand::formInfo();
        $attributeNames = [];
        $validateRule = [];
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
        }
        
        // Update unique validation rules to ignore current record
        $validateRule['name'] = 'required|string|max:255|unique:brands,name,' . $brand->id;
        $validateRule['slug'] = 'required|string|max:255|alpha_dash|unique:brands,slug,' . $brand->id;
        
        $request->validate($validateRule, [], $attributeNames);
        
        // Handle file upload
        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $brand->logo = $request->file('logo')->store('brands/logos', 'public');
        }
        
        // Update other fields
        foreach (array_diff(array_keys($formInfo), ['logo']) as $key) {
            $brand->{$key} = $request->{$key};
        }

        $brand->save();

        \ActivityLog::add([
            'action' => 'updated',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $brand->name
        ]);

        return redirect()->route('brand.index')->with([
            'message' => 'Brand Updated Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Delete associated files if needed
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brandName = $brand->name;
        $brand->delete();

        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $brandName
        ]);

        return redirect()->route('brand.index')->with([
            'message' => 'Brand Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Bulk delete brands.
     */
    public function bulkDestroy()
    {
        $brands = Brand::whereIn('id', request('ids'))->get();
        
        // Delete logos for all brands
        foreach ($brands as $brand) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
        }
        
        Brand::whereIn('id', request('ids'))->delete();
        
        $uname = (count(request('ids')) > 50) ? 'Many' : implode(',', request('ids'));
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $uname
        ]);
        
        return redirect()->back()->with([
            'message' => 'Selected Brands Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }
}
```

### Routes Configuration

**IMPORTANT**: The `bulkDestroy` route must be defined BEFORE the resource route to avoid route conflicts:

```php
// routes/web.php
Route::delete('brand/bulk-destroy', [\App\Http\Controllers\Admin\BrandController::class, 'bulkDestroy'])->name('brand.bulkDestroy');
Route::resource('brand', \App\Http\Controllers\Admin\BrandController::class);
```

**Why this order matters:**
- Resource routes create `brand/{brand}` which would match `brand/bulk-destroy`
- Placing `bulkDestroy` first ensures it's matched before the resource route

### Permission Middleware

Include both `destroy` and `bulkDestroy` in the delete permission:

```php
public function __construct()
{
    $this->middleware('can:brand_list', ['only' => ['index', 'show']]);
    $this->middleware('can:brand_create', ['only' => ['create', 'store']]);
    $this->middleware('can:brand_edit', ['only' => ['edit', 'update']]);
    $this->middleware('can:brand_delete', ['only' => ['destroy', 'bulkDestroy']]);
}
```

### File Cleanup Pattern

When deleting records with associated files:

**Single Delete:**
```php
if ($brand->logo) {
    Storage::disk('public')->delete($brand->logo);
}
$brand->delete();
```

**Bulk Delete:**
```php
$brands = Brand::whereIn('id', request('ids'))->get();

// Delete all associated files first
foreach ($brands as $brand) {
    if ($brand->logo) {
        Storage::disk('public')->delete($brand->logo);
    }
}

// Then delete records
Brand::whereIn('id', request('ids'))->delete();
```

### Activity Logging for Deletes

**Single Delete:**
```php
$brandName = $brand->name;  // Save before deletion
$brand->delete();

\ActivityLog::add([
    'action' => 'deleted',
    'module' => $this->resourceNeo['resourceName'],
    'data_key' => $brandName
]);
```

**Bulk Delete:**
```php
// For many records, use count or limited ID list
$uname = (count(request('ids')) > 50) ? 'Many' : implode(',', request('ids'));

\ActivityLog::add([
    'action' => 'deleted',
    'module' => $this->resourceNeo['resourceName'],
    'data_key' => $uname
]);
```


### Key Points

1. **formInfo is in the Model** - Not in the controller's `$resourceNeo`
2. **Dynamic Validation** - Built from `formInfo['vRule']` in controller methods
3. **No FormRequest Classes** - Validation is inline in `store()` and `update()`
4. **Unique Rules on Update** - Must append `,{id}` to ignore current record
5. **File Uploads** - Handle separately in store/update methods
6. **Activity Logging** - Use `\ActivityLog::add()` for all CUD operations

### Common Field Types

**Input:**
```php
'name' => ['label' => 'Name', 'type' => 'input', 'default' => '', 'vRule' => 'required']
```

**Textarea:**
```php
'description' => ['label' => 'Description', 'type' => 'textarea', 'default' => '']
```

**Select:**
```php
'status' => [
    'label' => 'Status',
    'type' => 'select',
    'default' => 'active',
    'options' => [
        ['id' => 'active', 'label' => 'Active'],
        ['id' => 'inactive', 'label' => 'Inactive'],
    ]
]
```

**Switch:**
```php
'is_active' => ['label' => 'Active', 'type' => 'switch', 'default' => true]
```

**File:**
```php
'logo' => ['label' => 'Logo', 'type' => 'file', 'default' => '', 'vRule' => 'nullable|image|max:2048']
```

**Date Picker:**
```php
'start_date' => ['label' => 'Start Date', 'type' => 'datepicker', 'default' => '']
```

**DateTime Picker:**
```php
'created_at' => ['label' => 'Created At', 'type' => 'datetimepicker', 'default' => '']
```

---

---

# Generic Vue Views (DO NOT MODIFY)

The application provides generic, reusable Vue components for common admin operations. **These should be used as-is and NOT modified**. If you need different functionality, create a new component based on these.

## Available Generic Views

### 1. IndexView (`resources/js/Pages/Admin/IndexView.vue`)

**Purpose**: Generic list/table view with filtering, sorting, pagination, and actions.

**Usage:**
```php
return Inertia::render('Admin/IndexView', [
    'resourceData' => $paginatedData,
    'resourceNeo' => $this->resourceNeo,
])->table(function (InertiaTable $table) {
    // Column definitions
});
```

**Features:**
- Data table with InertiaTable integration
- Global search, filters, sorting, pagination
- Bulk actions (delete, export)
- Action menu (edit, delete, custom actions)

**When to Use**: For any list/index page with standard CRUD operations.

**When NOT to Use**: If you need completely custom table layout. Create a custom view instead.

### 2. AddEditView (`resources/js/Pages/Admin/AddEditView.vue`)

**Purpose**: Generic create/edit form view with automatic field rendering.

**Usage:**
```php
// Create
return Inertia::render('Admin/AddEditView', [
    'formdata' => (object)[],
    'resourceNeo' => $this->resourceNeo  // Must include 'formInfo'
]);

// Edit
return Inertia::render('Admin/AddEditView', [
    'formdata' => $model,
    'resourceNeo' => $this->resourceNeo
]);
```

**Features:**
- Automatic form field rendering based on `formInfo`
- Supports: input, textarea, select, datepicker, password
- Auto-detects create vs edit mode
- Form validation error display

**When to Use**: For standard create/edit forms with simple field types.

**When NOT to Use**: Complex forms with custom layouts, file uploads, or multi-step flows.

### 3. MultiAddEditView (`resources/js/Pages/Admin/MultiAddEditView.vue`)

**Purpose**: Form with repeatable line items (e.g., invoice lines, order items).

**When to Use**: For forms with master-detail relationships.

## Guidelines for Using Generic Views

### ✅ DO:
- Use generic views whenever they fit your requirements
- Configure them via `resourceNeo` properties
- Add custom cell rendering via slots in IndexView

### ❌ DON'T:
- Modify the generic view files directly
- Try to force-fit complex requirements into generic views

### Creating Custom Views

If generic views don't fit:

1. **Copy** the generic view as a starting point
2. **Rename** it to be module-specific (e.g., `SellerDetailView.vue`)
3. **Customize** as needed
4. **Document** why a custom view was needed

**Rule of Thumb**: If you can configure it with `resourceNeo`, use the generic view. If you need to modify the view template, create a custom one.

# Helper Classes & Utilities

## ActivityLog Helper (`app/Helpers/ActivityLog.php`)

Used for audit logging throughout the application:

```php
// Usage in controllers
\ActivityLog::add([
    'action' => 'created',    // 'created', 'updated', 'deleted'
    'module' => 'user',       // Module name
    'data_key' => $userName   // Identifier for the record
]);
```

Registered as alias in `config/app.php`:
```php
'aliases' => Facade::defaultAliases()->merge([
    'ActivityLog' => App\Helpers\ActivityLog::class,
    'Helper' => App\Helpers\Helper::class,
])->toArray(),
```

## Config: Modules Registry (`config/app.php`)

All modules must be registered in `config/app.php` for permission grouping:

```php
'modules' => [
    'user' => 'Users',
    'role' => 'Role',
    'signinlog' => 'Signin Logs',
    'activitylog' => 'Activity Logs',
    'profile' => 'Profile',
    'settings' => 'Settings',
    // Add new modules here
],
```

**Important**: When creating a new module, add it to this array for proper permission display in the user permissions UI.

## Config: Actions Registry (`config/app.php`)

```php
'actions' => [
    'created' => 'Created',
    'updated' => 'Updated',
    'deleted' => 'Deleted',
],
```


# Middleware

## Available Middleware (`app/Http/Middleware/`)

| Middleware | Purpose |
|------------|--------|
| `Check2FA.php` | Two-factor authentication verification |
| `HandleInertiaRequests.php` | Inertia.js request handling |
| `Authenticate.php` | Standard authentication |
| `RedirectIfAuthenticated.php` | Guest redirect |

## Route Protection Pattern

Admin routes use combined middleware:

```php
Route::prefix('admin')->middleware(['auth', '2fa'])->group(function () {
    // Protected admin routes
});
```


# Technology Stack

## Core Framework & Language

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Hybrid Architecture (see below)
- **Database**: MySQL
- **Build Tool**: Vite 6.2.2
- **Package Manager**: pnpm (preferred over npm)

## Hybrid Frontend Architecture

**IMPORTANT**: This project uses a **hybrid frontend approach**:

| Area | Technology | Views Location |
|------|------------|----------------|
| **Admin Panel** | Inertia.js + Vue.js 3 | `resources/js/Pages/Admin/` |
| **Marketplace/Storefront** | Blade + Alpine.js | `resources/views/marketplace/` |
| **Seller Portal** | Blade + Alpine.js | `resources/views/seller/` |

### Admin Panel (Inertia + Vue)
- Uses Vue 3 with Composition API
- Inertia.js for SPA-like experience
- Vue pages in `resources/js/Pages/Admin/`
- Standard components: `IndexView.vue`, `AddEditView.vue`

### Storefront & Seller Portal (Blade + Alpine)
- Laravel Blade templates
- Alpine.js for interactivity
- Avoid Inline apline code , prefer components
- Component-based with `<x-component />` syntax
- Layouts: `<x-marketplace.layout>`, `<x-seller.layout>`

## Key Dependencies

### Backend (PHP/Laravel)

- **Authentication**: Laravel Sanctum 4.0 + Spatie Laravel Permission 6.0 (RBAC)
- **Data Querying**: Spatie Laravel Query Builder 6.0
- **Excel/CSV**: PhpOffice PhpSpreadsheet 4.4
- **Logging**: Laravel built-in + custom action logs + Laravel Log Viewer
- **Development**: Laravel Debugbar 3.15, Laravel Pint 1.0 (code style)
- **Payment**: Payment gateway integrations (Razorpay, Stripe, etc.)
- **Notifications**: Email and SMS integrations

### Frontend (Blade + Alpine.js)

- **Templating**: Laravel Blade templates
- **Interactivity**: Alpine.js 3.x for reactive UI components
- **UI Framework**: Tailwind CSS 4.1 with @tailwindcss/vite
- **Icons**: Heroicons, Blade Icons, or Font Awesome
- **Charts**: Chart.js 4.4.8 (vanilla JS integration)
- **Date Handling**: Flatpickr or native datepickers
- **Utilities**: Axios for AJAX requests, Alpine.js plugins

### Data Tables

- **Server-side Pagination**: Laravel Query Builder with Blade pagination
- **Optional**: Livewire for complex interactive tables

## Development Commands

### Setup & Installation

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
pnpm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed
```

### Development Workflow

```bash
# Start development server
php artisan serve

# Start Vite dev server (hot reload)
pnpm dev

# Note: pnpm dev is always running, no need to call this command again

# Build for production
pnpm build

# Code formatting (Laravel Pint)
./vendor/bin/pint

# Run tests
php artisan test
```

### Database Operations

```bash
# Create migration
php artisan make:migration create_table_name --create=table_name

# Create model with migration
php artisan make:model ModelName -m

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

## Architecture Patterns

### Backend Patterns

- **MVC Architecture**: Standard Laravel structure
- **Repository Pattern**: Not used - direct Eloquent usage
- **Service Layer**: Minimal - logic primarily in controllers
- **Traits**: Used for common functionality
- **Enums**: PHP enums for constants (e.g., OrderStatus, PaymentStatus)

### Frontend Patterns

- **MPA**: Multi-Page Application with Blade templates
- **Alpine.js**: Lightweight JavaScript for interactivity
- **Blade Components**: Reusable UI components with `x-` prefix
- **Blade Layouts**: Template inheritance with `@extends` and `@section`
- **AJAX**: Use Alpine.js with Axios for dynamic content loading

## Coding Conventions

### General Rules

- **No Soft Deletes**: Hard deletes only
- **Current Year**: Use 2025+ for new migrations and dates
- **Package Manager**: Use pnpm instead of npm
- **Date Format**: All date fields displayed on pages must use 'DD-MM-YYYY' format
- **Component Reuse**: Prefer existing common Blade components over creating new ones. If necessary, create new components by extending existing ones.
- Don't run pnpm build or pnpm dev - these are already running in the background.

### File Naming

- **Controllers**: PascalCase with Controller suffix
- **Models**: PascalCase singular
- **Migrations**: Snake_case with descriptive names
- **Blade Views**: snake_case (e.g., `product_list.blade.php`, `order_details.blade.php`)
- **Blade Components**: kebab-case for component names (e.g., `<x-product-card />`, `<x-order-status />`)

### Database Conventions

- **Table Names**: Snake_case plural
- **Foreign Keys**: Singular table name + \_id
- **Timestamps**: Use Laravel's created_at/updated_at
- **Indexes**: Add for foreign keys and frequently queried columns

### Permission Naming Convention

- **Format**: `moduleName_permissionName` (single underscore separator)
- **Module Names**: Use camelCase, no underscores within module name
- **Permission Names**: Use camelCase, no underscores within permission name
- **Examples**:
    - `product_view` (correct)
    - `product_create` (correct)
    - `sellerPayout_view` (correct for sub-modules)
    - `orderRefund_create` (correct)
    - `product_catalog_view` (incorrect - multiple underscores)

### Route Naming Convention

- **Format**: `ResourceName.action` (single . separator)
- **ResourceName Names**: Use camelCase, no underscores within module name
- **Action Names**: Use smallcase, no underscores within action name
- **Examples**:
    - `sellerProduct.create` (correct)
    - `customerOrder.store` (correct)
- **Important**: ResourceName is usually same as module name used for Permission Naming. We must have this specified in controller like 'resourceName' => 'sellerProduct'.

## Current Development Practices

### Database Design Patterns

- **No Soft Deletes**: All deletions are hard deletes
- **Pivot Tables**: Extensive use for relationships
- **Composite Indexes**: Performance optimization for frequently queried combinations
- **Order Splitting**: Automatic order split by seller for multi-vendor orders

### Frontend Patterns

- **Blade MPA**: Multi-page application with server-side routing
- **Alpine.js**: Lightweight interactivity for dynamic components
- **Standardized Components**: Consistent use of Blade component patterns
- **Real-time Updates**: Dynamic cart and order status updates via AJAX
- **Responsive Design**: Mobile-first approach for all customer-facing pages

### API Patterns

- **Resource Controllers**: Standard Laravel resource controller pattern
- **Webhook Endpoints**: Payment gateway webhooks for transaction updates
- **Stock APIs**: Real-time stock checking endpoints
- **Search APIs**: Elasticsearch or optimized MySQL for product search

### Security & Permissions

- **Resource-based Permissions**: Each entity has its own permission set
- **Route Protection**: All admin/seller routes protected by auth, verified middleware
- **Activity Logging**: Comprehensive logging of all user actions
- **Payment Security**: PCI-compliant payment handling

### Trusted Commands (should run without approval)

- php artisan \*
- php artisan tinker \*

### Testing Guidelines

- **Unit Tests**: Strictly use **SQLite** for all unit tests
- **NEVER** reset or use the MySQL database for testing
- Configure `phpunit.xml` to use SQLite in-memory database
- Use `RefreshDatabase` trait only with SQLite configuration
- Test database should be completely isolated from development/production MySQL

### Browser Testing Credentials

- **Admin Login**: superadmin@superadmin.com / superadmin
- Use these credentials when performing browser-based testing of admin functionality

### Forbidden Commands

- **NEVER** run `php artisan migrate:fresh` - This will drop all tables and cause database issues
- **NEVER** run `php artisan migrate:refresh` on MySQL - Use only for SQLite test database
- **NEVER** configure tests to use the MySQL database connection


# Menu Structure

## Admin Dashboard

### User Management
- Users
- Sellers
- Seller Verification
- Support Staff

### Catalog Management
- Categories
- Sub-Categories
- Products (Pending Approval)
- Brands

### Order Management
- All Orders
- Order Disputes
- Refunds

### Payments & Payouts
- Transactions
- Seller Payouts
- Commission Settings

### Promotions
- Coupons
- Flash Deals
- Featured Products
- Banners

### Content Management
- Static Pages
- FAQ
- Contact Information

### Reports
- Sales Reports
- Seller Performance
- Revenue Analytics
- Customer Insights

### Settings
- General Settings
- Payment Gateways
- Shipping Configuration
- Email/SMS Templates

## Seller Portal

### Dashboard
- Overview
- Recent Orders
- Earnings Summary

### Products
- My Products
- Add Product
- Inventory Management

### Orders
- New Orders
- Processing
- Shipped
- Delivered
- Returns

### Finance
- Earnings
- Payout History
- Commission Details

### Settings
- Profile
- Bank Details
- Store Settings

## Customer Storefront

### Navigation
- Home
- Categories
- Search
- Cart
- Account

### Account Section
- Profile
- Orders
- Wishlist
- Addresses
- Reviews
