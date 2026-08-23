<?php

namespace Database\Seeders;

use App\Models\ProductCategory;

class ProductCategorySeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->warn(PHP_EOL.'Creating product categories...');

        $categories = [
            ['slug' => 'electronics', 'name' => 'Electronics', 'description' => 'Electronic devices and accessories', 'sort_order' => 1, 'is_active' => true, 'meta_title' => 'Electronics', 'meta_description' => 'Browse our electronic products'],
            ['slug' => 'clothing', 'name' => 'Clothing', 'description' => 'Apparel and fashion items', 'sort_order' => 2, 'is_active' => true, 'meta_title' => 'Clothing', 'meta_description' => 'Shop for clothing and fashion'],
            ['slug' => 'home-garden', 'name' => 'Home & Garden', 'description' => 'Home decor and garden supplies', 'sort_order' => 3, 'is_active' => true, 'meta_title' => 'Home & Garden', 'meta_description' => 'Transform your home and garden'],
            ['slug' => 'sports-outdoors', 'name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear', 'sort_order' => 4, 'is_active' => true, 'meta_title' => 'Sports & Outdoors', 'meta_description' => 'Gear up for sports and outdoor adventures'],
            ['slug' => 'toys-games', 'name' => 'Toys & Games', 'description' => 'Toys, games, and collectibles', 'sort_order' => 5, 'is_active' => true, 'meta_title' => 'Toys & Games', 'meta_description' => 'Fun toys and games for all ages'],
            ['slug' => 'books-media', 'name' => 'Books & Media', 'description' => 'Books, music, and entertainment', 'sort_order' => 6, 'is_active' => true, 'meta_title' => 'Books & Media', 'meta_description' => 'Discover books and media'],
            ['slug' => 'health-beauty', 'name' => 'Health & Beauty', 'description' => 'Health and beauty products', 'sort_order' => 7, 'is_active' => true, 'meta_title' => 'Health & Beauty', 'meta_description' => 'Health and beauty essentials'],
            ['slug' => 'automotive', 'name' => 'Automotive', 'description' => 'Car parts and accessories', 'sort_order' => 8, 'is_active' => true, 'meta_title' => 'Automotive', 'meta_description' => 'Automotive parts and accessories'],
            ['slug' => 'food-beverages', 'name' => 'Food & Beverages', 'description' => 'Food items and beverages', 'sort_order' => 9, 'is_active' => true, 'meta_title' => 'Food & Beverages', 'meta_description' => 'Food and beverage products'],
            ['slug' => 'gifts-cards', 'name' => 'Gifts & Cards', 'description' => 'Gift items and gift cards', 'sort_order' => 10, 'is_active' => true, 'meta_title' => 'Gifts & Cards', 'meta_description' => 'Perfect gifts and gift cards'],
        ];

        foreach ($categories as $category) {
            ProductCategory::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }

        $this->command?->info('Product categories seeded successfully.');
    }
}
