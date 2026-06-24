<?php

namespace Database\Seeders;

use App\Enums\SaleMode;
use App\Models\Addon;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Store settings
        $store = Store::query()->updateOrCreate(
            ['slug' => 'dapur-nusantara'],
            [
                'name' => 'Dapur Nusantara',
                'description' => 'Menyediakan masakan tradisional khas Indonesia yang lezat, higienis, dan terjangkau untuk semua kalangan.',
                'whatsapp' => '6281234567890',
                'address' => 'Jl. Nusantara Raya No. 45, Bandung, Jawa Barat',
                'latitude' => -6.9174639,
                'longitude' => 107.6191228,
                'base_delivery_fee' => 5000,
                'delivery_fee_per_km' => 2000,
                'low_stock_threshold' => 5,
                'max_delivery_distance_meters' => 10000,
            ]
        );

        // 2. Seed Business Hours
        // 0 = Sunday, 1 = Monday, ... 6 = Saturday
        for ($day = 0; $day <= 6; $day++) {
            BusinessHour::query()->updateOrCreate(
                ['store_id' => $store->id, 'day_of_week' => $day],
                [
                    'open_time' => '09:00:00',
                    'close_time' => '21:00:00',
                    'is_closed' => $day === 0, // closed on Sunday
                ]
            );
        }

        // 3. Seed Categories
        $cat1 = Category::query()->updateOrCreate(['slug' => 'makanan-utama'], ['name' => 'Makanan Utama', 'description' => 'Menu makan siang dan malam yang mengenyangkan', 'is_active' => true, 'sort_order' => 1]);
        $cat2 = Category::query()->updateOrCreate(['slug' => 'cemilan'], ['name' => 'Cemilan', 'description' => 'Camilan ringan untuk menemani waktu santai Anda', 'is_active' => true, 'sort_order' => 2]);
        $cat3 = Category::query()->updateOrCreate(['slug' => 'minuman'], ['name' => 'Minuman', 'description' => 'Pelepas dahaga dingin dan hangat', 'is_active' => true, 'sort_order' => 3]);

        // 4. Seed Option Groups & Values
        $groupPedas = OptionGroup::query()->create([
            'name' => 'Level Pedas',
            'selection_type' => 'single',
            'is_required' => true,
            'min_selected' => 1,
            'max_selected' => 1,
        ]);
        OptionValue::query()->create(['option_group_id' => $groupPedas->id, 'name' => 'Tidak Pedas', 'price_delta' => 0, 'sort_order' => 1]);
        OptionValue::query()->create(['option_group_id' => $groupPedas->id, 'name' => 'Sedang (Cabe 3)', 'price_delta' => 0, 'sort_order' => 2]);
        OptionValue::query()->create(['option_group_id' => $groupPedas->id, 'name' => 'Pedas Gila (Cabe 10)', 'price_delta' => 1500, 'sort_order' => 3]);

        $groupGula = OptionGroup::query()->create([
            'name' => 'Level Gula',
            'selection_type' => 'single',
            'is_required' => true,
            'min_selected' => 1,
            'max_selected' => 1,
        ]);
        OptionValue::query()->create(['option_group_id' => $groupGula->id, 'name' => 'Less Sugar (50%)', 'price_delta' => 0, 'sort_order' => 1]);
        OptionValue::query()->create(['option_group_id' => $groupGula->id, 'name' => 'Normal Sugar', 'price_delta' => 0, 'sort_order' => 2]);

        $groupTopping = OptionGroup::query()->create([
            'name' => 'Topping Roti/Kentang',
            'selection_type' => 'multiple',
            'is_required' => false,
            'min_selected' => 0,
            'max_selected' => 3,
        ]);
        OptionValue::query()->create(['option_group_id' => $groupTopping->id, 'name' => 'Keju Parut', 'price_delta' => 2500, 'sort_order' => 1]);
        OptionValue::query()->create(['option_group_id' => $groupTopping->id, 'name' => 'Meses Cokelat', 'price_delta' => 2000, 'sort_order' => 2]);
        OptionValue::query()->create(['option_group_id' => $groupTopping->id, 'name' => 'Susu Kental Manis', 'price_delta' => 1500, 'sort_order' => 3]);

        // 5. Seed Addons
        $addonTelur = Addon::query()->updateOrCreate(['name' => 'Telur Ceplok'], ['price' => 3000, 'is_active' => true]);
        $addonKerupuk = Addon::query()->updateOrCreate(['name' => 'Kerupuk Kaleng'], ['price' => 1500, 'is_active' => true]);
        $addonEsBatu = Addon::query()->updateOrCreate(['name' => 'Ekstra Es Batu'], ['price' => 1000, 'is_active' => true]);

        // 6. Seed 8 Products
        // Product 1: Nasi Goreng Spesial
        $p1 = Product::query()->create([
            'category_id' => $cat1->id,
            'name' => 'Nasi Goreng Spesial',
            'slug' => 'nasi-goreng-spesial',
            'description' => 'Nasi goreng khas Dapur Nusantara dengan rempah tradisional, disajikan dengan acar segar.',
            'sale_mode' => SaleMode::ReadyStock,
            'is_active' => true,
            'is_featured' => true,
        ]);
        $p1->variants()->createMany([
            ['name' => 'Porsi Biasa', 'sku' => 'NASGOR-REG', 'price' => 15000, 'stock_on_hand' => 30],
            ['name' => 'Porsi Jumbo', 'sku' => 'NASGOR-JUMBO', 'price' => 20000, 'stock_on_hand' => 15],
        ]);
        $p1->optionGroups()->attach($groupPedas->id);
        $p1->addons()->attach([$addonTelur->id, $addonKerupuk->id]);

        // Product 2: Mie Goreng Aceh
        $p2 = Product::query()->create([
            'category_id' => $cat1->id,
            'name' => 'Mie Goreng Aceh',
            'slug' => 'mie-goreng-aceh',
            'description' => 'Mie kuning tebal khas Aceh dimasak dengan bumbu kari pekat yang kaya akan rempah.',
            'sale_mode' => SaleMode::Both,
            'is_active' => true,
            'is_featured' => false,
        ]);
        $p2->variants()->createMany([
            ['name' => 'Porsi Biasa', 'sku' => 'MIE-ACEH-REG', 'price' => 16000, 'stock_on_hand' => 20],
            ['name' => 'Porsi Double', 'sku' => 'MIE-ACEH-DBL', 'price' => 26000, 'stock_on_hand' => 10],
        ]);
        $p2->optionGroups()->attach($groupPedas->id);
        $p2->addons()->attach([$addonTelur->id, $addonKerupuk->id]);

        // Product 3: Sate Ayam Madura
        $p3 = Product::query()->create([
            'category_id' => $cat1->id,
            'name' => 'Sate Ayam Madura',
            'slug' => 'sate-ayam-madura',
            'description' => 'Sate ayam empuk yang dipanggang dengan bumbu kecap manis dan disiram saus kacang gurih lembut.',
            'sale_mode' => SaleMode::Preorder,
            'is_active' => true,
            'is_featured' => true,
        ]);
        $p3->variants()->createMany([
            ['name' => '10 Tusuk', 'sku' => 'SATE-10', 'price' => 18000, 'stock_on_hand' => 0],
            ['name' => '20 Tusuk', 'sku' => 'SATE-20', 'price' => 34000, 'stock_on_hand' => 0],
        ]);
        $p3->addons()->attach($addonKerupuk->id);

        // Product 4: Kentang Goreng
        $p4 = Product::query()->create([
            'category_id' => $cat2->id,
            'name' => 'Kentang Goreng Krispi',
            'slug' => 'kentang-goreng-krispi',
            'description' => 'Kentang potong impor digoreng garing dengan taburan garam gurih.',
            'sale_mode' => SaleMode::ReadyStock,
            'is_active' => true,
            'is_featured' => false,
        ]);
        $p4->variants()->create([
            'name' => 'Porsi Reguler', 'sku' => 'FRIES-REG', 'price' => 12000, 'stock_on_hand' => 50,
        ]);
        $p4->optionGroups()->attach($groupTopping->id);

        // Product 5: Roti Bakar Bandung
        $p5 = Product::query()->create([
            'category_id' => $cat2->id,
            'name' => 'Roti Bakar Bandung',
            'slug' => 'roti-bakar-bandung',
            'description' => 'Roti bakar khas Bandung dengan mentega melimpah dan berbagai pilihan topping.',
            'sale_mode' => SaleMode::Both,
            'is_active' => true,
            'is_featured' => true,
        ]);
        $p5->variants()->createMany([
            ['name' => 'Setengah Porsi', 'sku' => 'ROBAR-HALF', 'price' => 9000, 'stock_on_hand' => 15],
            ['name' => 'Satu Porsi', 'sku' => 'ROBAR-FULL', 'price' => 16000, 'stock_on_hand' => 12],
        ]);
        $p5->optionGroups()->attach($groupTopping->id);

        // Product 6: Pisang Goreng Keju
        $p6 = Product::query()->create([
            'category_id' => $cat2->id,
            'name' => 'Pisang Goreng Keju',
            'slug' => 'pisang-goreng-keju',
            'description' => 'Pisang kepok manis digoreng tepung renyah dengan taburan keju parut melimpah.',
            'sale_mode' => SaleMode::ReadyStock,
            'is_active' => true,
            'is_featured' => false,
        ]);
        $p6->variants()->create([
            'name' => 'Satu Porsi (isi 5)', 'sku' => 'PISGORE-KEJU', 'price' => 13000, 'stock_on_hand' => 25,
        ]);

        // Product 7: Es Kopi Susu Aren
        $p7 = Product::query()->create([
            'category_id' => $cat3->id,
            'name' => 'Es Kopi Susu Aren',
            'slug' => 'es-kopi-susu-aren',
            'description' => 'Espresso house blend dipadukan dengan susu segar premium dan gula aren cair murni.',
            'sale_mode' => SaleMode::ReadyStock,
            'is_active' => true,
            'is_featured' => true,
        ]);
        $p7->variants()->create([
            'name' => 'Reguler 12oz', 'sku' => 'KOPI-AREN-REG', 'price' => 15000, 'stock_on_hand' => 100,
        ]);
        $p7->optionGroups()->attach($groupGula->id);
        $p7->addons()->attach($addonEsBatu->id);

        // Product 8: Es Teh Manis
        $p8 = Product::query()->create([
            'category_id' => $cat3->id,
            'name' => 'Es Teh Manis Selasih',
            'slug' => 'es-teh-manis-selasih',
            'description' => 'Teh melati wangi diseduh segar dengan gula cair asli dan biji selasih.',
            'sale_mode' => SaleMode::ReadyStock,
            'is_active' => true,
            'is_featured' => false,
        ]);
        $p8->variants()->createMany([
            ['name' => 'Cup Sedang', 'sku' => 'TEH-MEDIUM', 'price' => 4000, 'stock_on_hand' => 200],
            ['name' => 'Cup Besar', 'sku' => 'TEH-LARGE', 'price' => 6000, 'stock_on_hand' => 150],
        ]);
        $p8->optionGroups()->attach($groupGula->id);
        $p8->addons()->attach($addonEsBatu->id);
    }
}
