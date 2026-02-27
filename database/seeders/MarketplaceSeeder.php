<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedBrands();
        $this->seedProducts();
    }

    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Gadgets, appliances, and more.',
                'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?q=80&w=800&auto=format&fit=crop',
                'sub' => [
                    ['name' => 'Mobiles & Tablets', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Laptops', 'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Audio & Headphones', 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Cameras', 'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Smart Home', 'image' => 'https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=800&auto=format&fit=crop'],
                ]
            ],
            [
                'name' => 'Fashion',
                'description' => 'Clothing, footwear, and accessories.',
                'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=800&auto=format&fit=crop',
                'sub' => [
                    ['name' => 'Men\'s Wear', 'image' => 'https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Women\'s Wear', 'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Kids\' Wear', 'image' => 'https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Footwear', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Watches & Accessories', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop'],
                ]
            ],
            [
                'name' => 'Home & Kitchen',
                'description' => 'Furniture, decor, and kitchenware.',
                'image' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?q=80&w=800&auto=format&fit=crop',
                'sub' => [
                    ['name' => 'Furniture', 'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Kitchen Appliances', 'image' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Home Decor', 'image' => 'https://images.unsplash.com/photo-1513519247481-1685022f4645?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Bedding & Linen', 'image' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?q=80&w=800&auto=format&fit=crop'],
                ]
            ],
            [
                'name' => 'Health & Beauty',
                'description' => 'Personal care, makeup, and wellness.',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=800&auto=format&fit=crop',
                'sub' => [
                    ['name' => 'Skincare', 'image' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Makeup', 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Hair Care', 'image' => 'https://images.unsplash.com/photo-1527799822394-4d1445614bc2?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Wellness & Proteins', 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=800&auto=format&fit=crop'],
                ]
            ],
            [
                'name' => 'Toys & Baby',
                'description' => 'Toys, games, and baby care.',
                'image' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?q=80&w=800&auto=format&fit=crop',
                'sub' => [
                    ['name' => 'Soft Toys', 'image' => 'https://images.unsplash.com/photo-1559440666-3d234674900a?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Board Games', 'image' => 'https://images.unsplash.com/photo-1610890716171-6b1bb71ff3bd?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Baby Care Products', 'image' => 'https://images.unsplash.com/photo-1519689689253-cf9b15ad1d7c?q=80&w=800&auto=format&fit=crop'],
                ]
            ],
            [
                'name' => 'Sports & Fitness',
                'description' => 'Gym equipment, sporst gear, and more.',
                'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=800&auto=format&fit=crop',
                'sub' => [
                    ['name' => 'Gym Equipment', 'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Cricket Gear', 'image' => 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?q=80&w=800&auto=format&fit=crop'],
                    ['name' => 'Football Gear', 'image' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=800&auto=format&fit=crop'],
                ]
            ],
        ];

        foreach ($categories as $index => $catData) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($catData['name'])],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'image' => $catData['image'],
                    'is_active' => true,
                    'featured' => rand(0, 1) === 1,
                    'display_order' => ($index + 1) * 10,
                ]
            );

            if (isset($catData['sub'])) {
                foreach ($catData['sub'] as $subIndex => $subData) {
                    Category::updateOrCreate(
                        ['slug' => Str::slug($subData['name'])],
                        [
                            'parent_id' => $parent->id,
                            'name' => $subData['name'],
                            'description' => "Quality " . $subData['name'],
                            'image' => $subData['image'],
                            'is_active' => true,
                            'featured' => rand(0, 3) === 1, // 25% chance for subcategories
                            'display_order' => ($subIndex + 1),
                        ]
                    );
                }
            }
        }
    }

    private function seedBrands(): void
    {
        $brands = [
            ['name' => 'Apple', 'logo' => 'https://images.unsplash.com/photo-1611472173362-3f53dbd65d80?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Samsung', 'logo' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Sony', 'logo' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Nike', 'logo' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Adidas', 'logo' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Ikea', 'logo' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'L\'Oreal', 'logo' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Nestle', 'logo' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'HP', 'logo' => 'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Canon', 'logo' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Dell', 'logo' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Puma', 'logo' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Xiaomi', 'logo' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'Zara', 'logo' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=400&auto=format&fit=crop'],
            ['name' => 'H&M', 'logo' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=400&auto=format&fit=crop'],
        ];

        foreach ($brands as $brandData) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($brandData['name'])],
                [
                    'name' => $brandData['name'],
                    'logo' => $brandData['logo'],
                    'description' => "Official products from " . $brandData['name'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedProducts(): void
    {
        // Get sellers, categories, and brands
        $sellers = Seller::all();
        if ($sellers->isEmpty()) {
            $this->command->warn('No sellers found. Please run SellerSeeder first.');
            return;
        }

        $categories = Category::whereNotNull('parent_id')->get(); // Get subcategories only
        $brands = Brand::all();

        // Product templates with variations
        $productTemplates = [
            // Electronics > Mobiles & Tablets (30 products)
            'mobiles-tablets' => [
                'brands' => ['Apple', 'Samsung', 'Xiaomi'],
                'products' => [
                    ['name' => 'iPhone 15 Pro Max', 'base_price' => 134900, 'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?q=80&w=800'],
                    ['name' => 'iPhone 14 Pro', 'base_price' => 119900, 'image' => 'https://images.unsplash.com/photo-1678652197831-2d180705cd2c?q=80&w=800'],
                    ['name' => 'iPhone 13', 'base_price' => 69900, 'image' => 'https://images.unsplash.com/photo-1632661674596-df8be070a5c5?q=80&w=800'],
                    ['name' => 'Samsung Galaxy S24 Ultra', 'base_price' => 124999, 'image' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?q=80&w=800'],
                    ['name' => 'Samsung Galaxy S23 FE', 'base_price' => 59999, 'image' => 'https://images.unsplash.com/photo-1610945264803-c22b62d2a7b3?q=80&w=800'],
                    ['name' => 'Samsung Galaxy A54', 'base_price' => 38999, 'image' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?q=80&w=800'],
                    ['name' => 'iPad Air M2', 'base_price' => 59900, 'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?q=80&w=800'],
                    ['name' => 'iPad Pro 12.9"', 'base_price' => 109900, 'image' => 'https://images.unsplash.com/photo-1561154464-82e9adf32764?q=80&w=800'],
                    ['name' => 'Xiaomi 14 Pro', 'base_price' => 79999, 'image' => 'https://images.unsplash.com/photo-1523206489230-c012c64b2b48?q=80&w=800'],
                    ['name' => 'Xiaomi 13T Pro', 'base_price' => 49999, 'image' => 'https://images.unsplash.com/photo-1512446816042-444d641267d4?q=80&w=800'],
                ],
                'variations' => ['64GB', '128GB', '256GB', '512GB'],
                'colors' => ['Black', 'White', 'Blue', 'Gold'],
            ],
            
            // Electronics > Laptops (25 products)
            'laptops' => [
                'brands' => ['Apple', 'Dell', 'HP'],
                'products' => [
                    ['name' => 'MacBook Pro 16" M3', 'base_price' => 249900, 'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=800'],
                    ['name' => 'MacBook Air M2', 'base_price' => 114900, 'image' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?q=80&w=800'],
                    ['name' => 'MacBook Pro 14" M3', 'base_price' => 199900, 'image' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?q=80&w=800'],
                    ['name' => 'Dell XPS 15', 'base_price' => 149999, 'image' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=800'],
                    ['name' => 'Dell Inspiron 15', 'base_price' => 54999, 'image' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?q=80&w=800'],
                    ['name' => 'HP Spectre x360', 'base_price' => 129999, 'image' => 'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?q=80&w=800'],
                    ['name' => 'HP Pavilion 15', 'base_price' => 59999, 'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=800'],
                ],
                'variations' => ['i5/8GB/512GB', 'i7/16GB/1TB', 'i9/32GB/2TB'],
            ],
            
            // Electronics > Audio & Headphones (20 products)
            'audio-headphones' => [
                'brands' => ['Apple', 'Sony', 'Samsung'],
                'products' => [
                    ['name' => 'AirPods Pro 2nd Gen', 'base_price' => 24900, 'image' => 'https://images.unsplash.com/photo-1606841837239-c5a1a4a07af7?q=80&w=800'],
                    ['name' => 'AirPods Max', 'base_price' => 59900, 'image' => 'https://images.unsplash.com/photo-1625245488600-f89b8cb4b3a5?q=80&w=800'],
                    ['name' => 'AirPods 3rd Gen', 'base_price' => 19900, 'image' => 'https://images.unsplash.com/photo-1588423771073-b8903fbb85b5?q=80&w=800'],
                    ['name' => 'Sony WH-1000XM5', 'base_price' => 29990, 'image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?q=80&w=800'],
                    ['name' => 'Sony WF-1000XM4', 'base_price' => 19990, 'image' => 'https://images.unsplash.com/photo-1590658833571-04286701a50c?q=80&w=800'],
                    ['name' => 'Samsung Galaxy Buds2 Pro', 'base_price' => 14999, 'image' => 'https://images.unsplash.com/photo-1606220838315-056192d5e927?q=80&w=800'],
                ],
                'variations' => ['Black', 'White', 'Silver'],
            ],
            
            // Electronics > Cameras (15 products)
            'cameras' => [
                'brands' => ['Canon', 'Sony'],
                'products' => [
                    ['name' => 'Canon EOS R6 Mark II', 'base_price' => 229990, 'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=800'],
                    ['name' => 'Canon EOS R5', 'base_price' => 329990, 'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=800'],
                    ['name' => 'Canon EOS R10', 'base_price' => 89990, 'image' => 'https://images.unsplash.com/photo-1616628188506-4af8551978f1?q=80&w=800'],
                    ['name' => 'Sony Alpha A7 IV', 'base_price' => 209990, 'image' => 'https://images.unsplash.com/photo-1581591524423-cff6f483cc5a?q=80&w=800'],
                    ['name' => 'Sony Alpha A7R V', 'base_price' => 349990, 'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?q=80&w=800'],
                ],
                'variations' => ['Body Only', 'With 24-70mm Lens', 'With 70-200mm Lens'],
            ],
            
            // Fashion > Men's Wear (25 products)
            'mens-wear' => [
                'brands' => ['Nike', 'Adidas', 'Puma', 'Zara', 'H&M'],
                'products' => [
                    ['name' => 'Nike Dri-FIT Running Tee', 'base_price' => 1999, 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=800'],
                    ['name' => 'Nike Tech Fleece Hoodie', 'base_price' => 5999, 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=800'],
                    ['name' => 'Adidas Essentials Hoodie', 'base_price' => 3499, 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=800'],
                    ['name' => 'Adidas Training Shorts', 'base_price' => 1799, 'image' => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?q=80&w=800'],
                    ['name' => 'Puma Tracksuit Set', 'base_price' => 4999, 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=800'],
                    ['name' => 'Zara Formal Shirt', 'base_price' => 2499, 'image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=800'],
                    ['name' => 'H&M Casual Jeans', 'base_price' => 1999, 'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=800'],
                ],
                'variations' => ['S', 'M', 'L', 'XL', 'XXL'],
                'colors' => ['Black', 'White', 'Navy', 'Grey'],
            ],
            
            // Fashion > Women's Wear (25 products)
            'womens-wear' => [
                'brands' => ['Zara', 'H&M', 'Nike'],
                'products' => [
                    ['name' => 'Zara Floral Summer Dress', 'base_price' => 2999, 'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=800'],
                    ['name' => 'Zara Midi Skirt', 'base_price' => 1999, 'image' => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?q=80&w=800'],
                    ['name' => 'H&M Denim Jacket', 'base_price' => 2499, 'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=800'],
                    ['name' => 'H&M Casual Top', 'base_price' => 999, 'image' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?q=80&w=800'],
                    ['name' => 'Nike Women\'s Leggings', 'base_price' => 2799, 'image' => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?q=80&w=800'],
                    ['name' => 'Nike Sports Bra', 'base_price' => 1999, 'image' => 'https://images.unsplash.com/photo-1588117305388-c2631a279f82?q=80&w=800'],
                ],
                'variations' => ['XS', 'S', 'M', 'L', 'XL'],
                'colors' => ['Black', 'White', 'Pink', 'Blue'],
            ],
            
            // Fashion > Footwear (20 products)
            'footwear' => [
                'brands' => ['Nike', 'Adidas', 'Puma'],
                'products' => [
                    ['name' => 'Nike Air Max 270', 'base_price' => 12995, 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800'],
                    ['name' => 'Nike Air Force 1', 'base_price' => 8995, 'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=800'],
                    ['name' => 'Nike Revolution 6', 'base_price' => 4995, 'image' => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800'],
                    ['name' => 'Adidas Ultraboost 23', 'base_price' => 16999, 'image' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=800'],
                    ['name' => 'Adidas Superstar', 'base_price' => 7999, 'image' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=800'],
                    ['name' => 'Puma RS-X Sneakers', 'base_price' => 8999, 'image' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=800'],
                ],
                'variations' => ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10'],
            ],
            
            // Home & Kitchen > Furniture (15 products)
            'furniture' => [
                'brands' => ['Ikea'],
                'products' => [
                    ['name' => 'IKEA POÄNG Armchair', 'base_price' => 12999, 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=800'],
                    ['name' => 'IKEA MALM Bed Frame', 'base_price' => 24999, 'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=800'],
                    ['name' => 'IKEA BILLY Bookcase', 'base_price' => 7999, 'image' => 'https://images.unsplash.com/photo-1594620302200-9a762244a156?q=80&w=800'],
                    ['name' => 'IKEA HEMNES Dresser', 'base_price' => 19999, 'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?q=80&w=800'],
                    ['name' => 'IKEA KALLAX Shelf Unit', 'base_price' => 9999, 'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=800'],
                ],
                'variations' => ['White', 'Black-Brown', 'Oak'],
            ],
            
            // Home & Kitchen > Kitchen Appliances (15 products)
            'kitchen-appliances' => [
                'brands' => ['Samsung', 'Nestle'],
                'products' => [
                    ['name' => 'Samsung Microwave Oven', 'base_price' => 8999, 'image' => 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?q=80&w=800'],
                    ['name' => 'Samsung Refrigerator', 'base_price' => 34999, 'image' => 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?q=80&w=800'],
                    ['name' => 'Samsung Dishwasher', 'base_price' => 29999, 'image' => 'https://images.unsplash.com/photo-1556911220-bff31c812dba?q=80&w=800'],
                    ['name' => 'Nestle Coffee Maker', 'base_price' => 12999, 'image' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?q=80&w=800'],
                    ['name' => 'Nestle Espresso Machine', 'base_price' => 24999, 'image' => 'https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?q=80&w=800'],
                ],
                'variations' => ['Silver', 'Black', 'White'],
            ],
            
            // Health & Beauty > Skincare (10 products)
            'skincare' => [
                'brands' => ['L\'Oreal'],
                'products' => [
                    ['name' => 'L\'Oreal Revitalift Serum', 'base_price' => 1299, 'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?q=80&w=800'],
                    ['name' => 'L\'Oreal UV Perfect Sunscreen', 'base_price' => 799, 'image' => 'https://images.unsplash.com/photo-1571875257727-256c39da42af?q=80&w=800'],
                    ['name' => 'L\'Oreal Hydra Fresh Toner', 'base_price' => 599, 'image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=800'],
                    ['name' => 'L\'Oreal White Perfect Day Cream', 'base_price' => 999, 'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?q=80&w=800'],
                ],
                'variations' => ['30ml', '50ml', '100ml'],
            ],
            
            // Health & Beauty > Makeup (10 products)
            'makeup' => [
                'brands' => ['L\'Oreal'],
                'products' => [
                    ['name' => 'L\'Oreal Infallible Foundation', 'base_price' => 1099, 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=800'],
                    ['name' => 'L\'Oreal Kajal Magique', 'base_price' => 299, 'image' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=800'],
                    ['name' => 'L\'Oreal Color Riche Lipstick', 'base_price' => 699, 'image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?q=80&w=800'],
                    ['name' => 'L\'Oreal Voluminous Mascara', 'base_price' => 899, 'image' => 'https://images.unsplash.com/photo-1631214500115-598e9663d2e6?q=80&w=800'],
                ],
                'variations' => ['Shade 1', 'Shade 2', 'Shade 3'],
            ],
            
            // Sports & Fitness > Gym Equipment (10 products)
            'gym-equipment' => [
                'brands' => ['Nike', 'Adidas', 'Puma'],
                'products' => [
                    ['name' => 'Nike Yoga Mat', 'base_price' => 2499, 'image' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?q=80&w=800'],
                    ['name' => 'Adidas Dumbbells Set', 'base_price' => 4999, 'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=800'],
                    ['name' => 'Puma Resistance Bands', 'base_price' => 1299, 'image' => 'https://images.unsplash.com/photo-1598971639058-fab3c3109a00?q=80&w=800'],
                    ['name' => 'Nike Gym Bag', 'base_price' => 2999, 'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800'],
                ],
                'variations' => ['Small', 'Medium', 'Large'],
            ],
        ];

        $productCount = 0;

        // Generate products from templates
        foreach ($productTemplates as $categorySlug => $template) {
            $category = Category::where('slug', $categorySlug)->first();
            
            if (!$category) {
                continue;
            }

            foreach ($template['products'] as $productData) {
                // Get brand - try to find it in the name first, otherwise use template default
                $brandName = $productData['brand'] ?? null;
                if (!$brandName) {
                    foreach ($template['brands'] as $possibleBrand) {
                        if (stripos($productData['name'], $possibleBrand) !== false) {
                            $brandName = $possibleBrand;
                            break;
                        }
                    }
                }
                
                $brand = Brand::where('name', $brandName ?? $template['brands'][0])->first();
                
                // Create base product
                $seller = $sellers->random();
                $this->createProduct(
                    $productData['name'],
                    $category,
                    $brand,
                    $seller,
                    $productData['base_price'],
                    $productData['image']
                );
                $productCount++;

                // Create variations if defined
                if (isset($template['variations'])) {
                    $variationsToCreate = min(3, count($template['variations'])); // Limit to 3 variations per product
                    for ($i = 0; $i < $variationsToCreate; $i++) {
                        $variation = $template['variations'][$i];
                        $seller = $sellers->random();
                        $priceVariation = rand(-10, 20) * 100; // Price variation
                        
                        $this->createProduct(
                            $productData['name'] . ' - ' . $variation,
                            $category,
                            $brand,
                            $seller,
                            $productData['base_price'] + $priceVariation,
                            $productData['image']
                        );
                        $productCount++;
                    }
                }

                // Create color variations if defined
                if (isset($template['colors']) && rand(0, 1)) {
                    $colorsToCreate = min(2, count($template['colors'])); // Limit to 2 colors
                    for ($i = 0; $i < $colorsToCreate; $i++) {
                        $color = $template['colors'][$i];
                        $seller = $sellers->random();
                        
                        $this->createProduct(
                            $productData['name'] . ' - ' . $color,
                            $category,
                            $brand,
                            $seller,
                            $productData['base_price'],
                            $productData['image']
                        );
                        $productCount++;
                    }
                }
            }
        }

        $this->command->info("Successfully seeded {$productCount} products!");
    }

    /**
     * Helper method to create a product
     */
    private function createProduct($name, $category, $brand, $seller, $price, $image)
    {
        $comparePrice = $price + rand(500, 5000);
        
        Product::updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'short_description' => 'Premium quality ' . $name . ' with excellent features and performance.',
                'description' => 'Experience the best with ' . $name . '. This product combines cutting-edge technology with superior craftsmanship to deliver exceptional value. Perfect for both personal and professional use.',
                'category_id' => $category->id,
                'brand_id' => $brand?->id,
                'seller_id' => $seller->user_id, // seller_id references users.id, not sellers.id
                'price' => $price,
                'compare_at_price' => $comparePrice,
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'barcode' => rand(1000000000000, 9999999999999),
                'hsn_code' => rand(1000, 9999),
                'gst_rate' => 18.00,
                'is_inclusive_tax' => true,
                'quantity' => rand(10, 100),
                'stock_status' => 'in_stock',
                'is_active' => true,
                'is_featured' => rand(0, 4) == 1, // 20% chance of being featured
                'requires_shipping' => true,
                'weight' => rand(100, 5000) / 100,
                'length' => rand(10, 50),
                'width' => rand(10, 50),
                'height' => rand(5, 30),
                'is_fragile' => rand(0, 3) == 1, // 25% chance of being fragile
                'main_image' => $image . '&auto=format&fit=crop',
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => 1,
            ]
        );
    }
}
