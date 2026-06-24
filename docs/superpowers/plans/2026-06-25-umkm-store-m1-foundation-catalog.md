# UMKM Store Milestone 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun fondasi Laravel, autentikasi admin, pengaturan toko, katalog lengkap, stok awal, dan storefront mobile-first dengan desktop layout penuh.

**Architecture:** Aplikasi Laravel modular monolith menggunakan Blade dan Livewire. Aturan nilai dasar ditempatkan pada enum/value object, operasi katalog dipisahkan dari UI, dan Eloquent menyimpan data pada MySQL.

**Tech Stack:** Laravel 13, PHP 8.3/8.4, MySQL 8, Livewire 4, Alpine.js bawaan Livewire, Tailwind CSS 4, Vite, Pest/PHPUnit.

## Global Constraints

- Lingkungan lokal menggunakan Laragon 13.
- Nama class, file, tabel, dan kolom teknis menggunakan bahasa Inggris.
- Copy antarmuka menggunakan Bahasa Indonesia.
- Uang disimpan sebagai integer rupiah.
- Mobile baseline harus berfungsi pada 360, 390, dan 430 CSS pixel.
- Desktop 1280 dan 1440 CSS pixel harus memakai layout desktop penuh.
- Target sentuh aksi utama minimal 44 × 44 CSS pixel.
- Fungsi inti tidak boleh hanya tersedia melalui hover.
- Setiap perubahan perilaku dimulai dengan failing test.
- Jangan menambah package di luar rencana tanpa alasan yang tercatat.

---

## File Structure

```text
app/
├── Enums/SaleMode.php
├── Livewire/Admin/
├── Livewire/Storefront/
├── Models/
├── Services/Catalog/
└── ValueObjects/Money.php
database/
├── factories/
├── migrations/
└── seeders/
resources/
├── css/app.css
├── js/app.js
└── views/
    ├── components/
    ├── layouts/
    └── livewire/
routes/
├── admin.php
└── web.php
tests/
├── Feature/Admin/
├── Feature/Storefront/
└── Unit/
```

## Task 1: Scaffold dan Health Check

**Files:**

- Create: Laravel application files in repository root
- Modify: `composer.json`
- Modify: `package.json`
- Modify: `.env.example`
- Modify: `routes/web.php`
- Test: `tests/Feature/HealthTest.php`

**Interfaces:**

- Produces: Laravel app yang dapat boot, database test, Livewire, dan asset build.

- [ ] **Step 1: Buat failing health test**

```php
<?php

it('serves the storefront home page', function () {
    $this->get('/')->assertOk()->assertSee('UMKM Store');
});
```

- [ ] **Step 2: Jalankan test**

Run:

```powershell
php artisan test tests/Feature/HealthTest.php
```

Expected: FAIL karena project/route belum tersedia.

- [ ] **Step 3: Scaffold dependency**

Run from an empty temporary directory:

```powershell
composer create-project laravel/laravel:^13.0 work/laravel-app
composer require livewire/livewire:^4.0 --working-dir=work/laravel-app
npm install --prefix work/laravel-app
npm install tailwindcss @tailwindcss/vite --prefix work/laravel-app
```

Copy the generated application entries into the repository one by one. Keep the existing `.git`, `docs`, `outputs`, and `work` directories:

```powershell
Get-ChildItem -LiteralPath 'work\laravel-app' -Force |
    Where-Object { $_.Name -notin @('.git', 'docs', 'outputs', 'work') } |
    ForEach-Object {
        Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $PWD $_.Name) -Recurse -Force
    }
```

Before running the copy command, verify that `$PWD` is the UMKM Store repository and `work\laravel-app` contains `artisan` and `composer.json`.

Configure `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

Set `resources/css/app.css`:

```css
@import "tailwindcss";

@source "../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php";
@source "../../storage/framework/views/*.php";
@source "../**/*.blade.php";
@source "../**/*.js";
```

Set `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'storefront.home')->name('storefront.home');
```

Create `resources/views/storefront/home.blade.php`:

```blade
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UMKM Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <h1>UMKM Store</h1>
</body>
</html>
```

- [ ] **Step 4: Konfigurasi test database**

Use SQLite in-memory only for fast non-concurrency tests in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="CACHE_STORE" value="array"/>
<env name="SESSION_DRIVER" value="array"/>
```

MySQL-specific and concurrency tests will use a separate `.env.testing.mysql` in later milestones.

- [ ] **Step 5: Verifikasi**

Run:

```powershell
php artisan test tests/Feature/HealthTest.php
npm run build
```

Expected: test PASS and Vite build exits with code 0.

- [ ] **Step 6: Commit**

```powershell
git add .
git commit -m "chore: scaffold Laravel storefront"
```

## Task 2: Shared Enums dan Value Objects

**Files:**

- Create: `app/Enums/SaleMode.php`
- Create: `app/ValueObjects/Money.php`
- Create: `app/ValueObjects/NormalizedPhone.php`
- Test: `tests/Unit/ValueObjects/MoneyTest.php`
- Test: `tests/Unit/ValueObjects/NormalizedPhoneTest.php`

**Interfaces:**

- Produces: `Money::fromRupiah(int)`, `Money::add(Money)`, `Money::format()`, `NormalizedPhone::from(string)`.

- [ ] **Step 1: Tulis failing tests**

```php
<?php

use App\ValueObjects\Money;

it('adds and formats rupiah', function () {
    $total = Money::fromRupiah(10000)->add(Money::fromRupiah(2500));

    expect($total->rupiah())->toBe(12500)
        ->and($total->format())->toBe('Rp12.500');
});

it('rejects negative public money', function () {
    Money::fromRupiah(-1);
})->throws(InvalidArgumentException::class);
```

```php
<?php

use App\ValueObjects\NormalizedPhone;

it('normalizes Indonesian WhatsApp numbers', function () {
    expect((string) NormalizedPhone::from('0812-3456-7890'))
        ->toBe('6281234567890');
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Unit/ValueObjects
```

Expected: FAIL karena class belum ada.

- [ ] **Step 3: Implementasikan enum dan value object**

```php
<?php

namespace App\Enums;

enum SaleMode: string
{
    case ReadyStock = 'ready_stock';
    case Preorder = 'preorder';
    case Both = 'both';
}
```

```php
<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(private int $rupiah) {}

    public static function fromRupiah(int $rupiah): self
    {
        if ($rupiah < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }

        return new self($rupiah);
    }

    public function add(self $other): self
    {
        return new self($this->rupiah + $other->rupiah);
    }

    public function rupiah(): int
    {
        return $this->rupiah;
    }

    public function format(): string
    {
        return 'Rp'.number_format($this->rupiah, 0, ',', '.');
    }
}
```

```php
<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class NormalizedPhone
{
    private function __construct(private string $value) {}

    public static function from(string $phone): self
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if (! preg_match('/^628\d{8,11}$/', $digits)) {
            throw new InvalidArgumentException('Invalid Indonesian phone number.');
        }

        return new self($digits);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

- [ ] **Step 4: Verifikasi dan commit**

```powershell
php artisan test tests/Unit/ValueObjects
git add app/Enums app/ValueObjects tests/Unit/ValueObjects
git commit -m "feat: add shared catalog value objects"
```

Expected: PASS.

## Task 3: Autentikasi Satu Admin

**Files:**

- Create: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Create: `resources/views/auth/login.blade.php`
- Create: `resources/views/admin/dashboard.blade.php`
- Create: `resources/views/layouts/admin.blade.php`
- Create: `routes/admin.php`
- Modify: `bootstrap/app.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/Admin/AuthenticationTest.php`

**Interfaces:**

- Produces: route `admin.login`, `admin.dashboard`, session auth, seeded admin.

- [ ] **Step 1: Tulis failing feature tests**

```php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects guests away from admin dashboard', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('allows the seeded admin to login and logout', function () {
    User::factory()->create([
        'email' => 'admin@umkm.test',
        'password' => Hash::make('password'),
    ]);

    $this->post('/admin/login', [
        'email' => 'admin@umkm.test',
        'password' => 'password',
    ])->assertRedirect('/admin');

    $this->assertAuthenticated();
    $this->post('/admin/logout')->assertRedirect('/admin/login');
    $this->assertGuest();
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Feature/Admin/AuthenticationTest.php
```

Expected: FAIL karena route belum ada.

- [ ] **Step 3: Tambahkan route admin**

`routes/admin.php`:

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

Register it in `bootstrap/app.php`:

```php
use Illuminate\Support\Facades\Route;

->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function (): void {
        Route::middleware('web')
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
    },
)
```

Use Laravel's standard authenticated session controller behavior: validate email/password, call `Auth::attempt`, regenerate session, invalidate session on logout, and regenerate CSRF token.

`AuthenticatedSessionController`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, false)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
```

- [ ] **Step 4: Seed satu admin**

```php
User::query()->updateOrCreate(
    ['email' => env('ADMIN_EMAIL', 'admin@umkm.test')],
    [
        'name' => 'Admin UMKM',
        'password' => Hash::make(env('ADMIN_PASSWORD', 'change-me-now')),
    ],
);
```

Add to `.env.example`:

```dotenv
ADMIN_EMAIL=admin@umkm.test
ADMIN_PASSWORD=change-me-now
```

- [ ] **Step 5: Verifikasi dan commit**

```powershell
php artisan test tests/Feature/Admin/AuthenticationTest.php
git add app routes bootstrap database resources tests .env.example
git commit -m "feat: add single admin authentication"
```

Expected: PASS.

## Task 4: Store Settings

**Files:**

- Create: migration `create_stores_table`
- Create: migration `create_business_hours_table`
- Create: `app/Models/Store.php`
- Create: `app/Models/BusinessHour.php`
- Create: `app/Livewire/Admin/StoreSettingsPage.php`
- Create: `resources/views/livewire/admin/store-settings-page.blade.php`
- Test: `tests/Feature/Admin/StoreSettingsTest.php`

**Interfaces:**

- Produces: `Store::current(): Store`, route `admin.settings`, editable store profile.

- [ ] **Step 1: Tulis failing test**

```php
<?php

use App\Models\Store;
use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Admin\StoreSettingsPage;

it('allows admin to update store settings', function () {
    $this->actingAs(User::factory()->create());
    Store::factory()->create();

    Livewire::test(StoreSettingsPage::class)
        ->set('name', 'Dapur Rasa')
        ->set('whatsapp', '081234567890')
        ->set('address', 'Jakarta')
        ->set('latitude', -6.2000000)
        ->set('longitude', 106.8166667)
        ->set('baseDeliveryFee', 5000)
        ->set('deliveryFeePerKm', 2500)
        ->call('save')
        ->assertHasNoErrors();

    expect(Store::first()->name)->toBe('Dapur Rasa')
        ->and(Store::first()->max_delivery_distance_meters)->toBe(10000);
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Feature/Admin/StoreSettingsTest.php
```

Expected: FAIL karena schema/component belum ada.

- [ ] **Step 3: Buat schema**

Migration stores harus membuat:

```php
$table->id();
$table->string('name', 150);
$table->string('slug', 160)->unique();
$table->text('description')->nullable();
$table->string('logo_path')->nullable();
$table->string('whatsapp', 20);
$table->text('address');
$table->decimal('latitude', 10, 7);
$table->decimal('longitude', 10, 7);
$table->string('timezone', 64)->default('Asia/Jakarta');
$table->unsignedBigInteger('base_delivery_fee')->default(0);
$table->unsignedBigInteger('delivery_fee_per_km')->default(0);
$table->unsignedInteger('max_delivery_distance_meters')->default(10000);
$table->unsignedInteger('low_stock_threshold')->default(5);
$table->timestamps();
```

`Store::current()`:

```php
public static function current(): self
{
    return static::query()->firstOrFail();
}
```

- [ ] **Step 4: Implementasikan component dengan validation**

Rules:

```php
[
    'name' => ['required', 'string', 'max:150'],
    'whatsapp' => ['required', 'string', 'max:20'],
    'address' => ['required', 'string', 'max:1000'],
    'latitude' => ['required', 'numeric', 'between:-90,90'],
    'longitude' => ['required', 'numeric', 'between:-180,180'],
    'baseDeliveryFee' => ['required', 'integer', 'min:0'],
    'deliveryFeePerKm' => ['required', 'integer', 'min:0'],
]
```

Force `max_delivery_distance_meters` to `10000` on save.

- [ ] **Step 5: Verifikasi dan commit**

```powershell
php artisan test tests/Feature/Admin/StoreSettingsTest.php
git add app database resources routes tests
git commit -m "feat: add store settings management"
```

## Task 5: Catalog Database Model

**Files:**

- Create migrations for `categories`, `products`, `product_images`, `product_variants`
- Create models and factories for each table
- Test: `tests/Feature/Catalog/CatalogSchemaTest.php`

**Interfaces:**

- Produces: Eloquent relationships and catalog factory states.

- [ ] **Step 1: Tulis failing relationship test**

```php
<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

it('persists product variants under a category', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $variant = ProductVariant::factory()->for($product)->create([
        'price' => 18000,
        'stock_on_hand' => 10,
        'reserved_stock' => 2,
    ]);

    expect($category->products)->toHaveCount(1)
        ->and($product->variants)->toHaveCount(1)
        ->and($variant->available_stock)->toBe(8);
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Feature/Catalog/CatalogSchemaTest.php
```

Expected: FAIL.

- [ ] **Step 3: Buat migration sesuai SDD**

Minimum columns:

```php
// categories
$table->id();
$table->string('name', 100);
$table->string('slug', 120)->unique();
$table->text('description')->nullable();
$table->boolean('is_active')->default(true)->index();
$table->unsignedInteger('sort_order')->default(0);
$table->timestamps();
$table->softDeletes();

// products
$table->id();
$table->foreignId('category_id')->constrained();
$table->string('name', 150);
$table->string('slug', 170)->unique();
$table->text('description')->nullable();
$table->string('sale_mode', 20)->index();
$table->boolean('is_active')->default(true)->index();
$table->boolean('is_featured')->default(false);
$table->timestamps();
$table->softDeletes();

// product_variants
$table->id();
$table->foreignId('product_id')->constrained();
$table->string('name', 100);
$table->string('sku', 100)->unique();
$table->unsignedBigInteger('price');
$table->unsignedInteger('stock_on_hand')->default(0);
$table->unsignedInteger('reserved_stock')->default(0);
$table->boolean('is_active')->default(true);
$table->unsignedInteger('sort_order')->default(0);
$table->timestamps();
$table->softDeletes();
```

Accessor:

```php
protected function availableStock(): Attribute
{
    return Attribute::get(
        fn (): int => max(0, $this->stock_on_hand - $this->reserved_stock),
    );
}
```

- [ ] **Step 4: Verifikasi dan commit**

```powershell
php artisan migrate:fresh
php artisan test tests/Feature/Catalog/CatalogSchemaTest.php
git add app/Models database tests/Feature/Catalog
git commit -m "feat: add core catalog data model"
```

## Task 6: Product Options dan Add-ons

**Files:**

- Create migrations for `option_groups`, `option_values`, `product_option_groups`, `addons`, `product_addons`
- Create related models and factories
- Test: `tests/Feature/Catalog/ProductConfigurationTest.php`

**Interfaces:**

- Produces: `Product::optionGroups()`, `Product::addons()`, active option scopes.

- [ ] **Step 1: Tulis failing test**

```php
<?php

use App\Models\Addon;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use App\Models\Product;

it('loads active options and addons for a product', function () {
    $product = Product::factory()->create();
    $group = OptionGroup::factory()->create([
        'name' => 'Level Pedas',
        'is_required' => true,
        'min_selected' => 1,
        'max_selected' => 1,
    ]);
    OptionValue::factory()->for($group)->create(['name' => 'Pedas']);
    $addon = Addon::factory()->create(['name' => 'Keju', 'price' => 3000]);

    $product->optionGroups()->attach($group);
    $product->addons()->attach($addon);

    expect($product->load('optionGroups.values', 'addons')->optionGroups)->toHaveCount(1)
        ->and($product->addons)->toHaveCount(1);
});
```

- [ ] **Step 2: Jalankan, implementasikan schema/relationship, lalu verifikasi**

```powershell
php artisan test tests/Feature/Catalog/ProductConfigurationTest.php
```

Expected before implementation: FAIL.

Schema rules:

- `selection_type` only `single` or `multiple`.
- `min_selected <= max_selected`.
- `price_delta` and add-on price are unsigned rupiah.
- Pivot pairs are unique.

Run after implementation:

```powershell
php artisan migrate:fresh
php artisan test tests/Feature/Catalog/ProductConfigurationTest.php
git add app/Models database tests/Feature/Catalog
git commit -m "feat: add product options and addons"
```

Expected: PASS.

## Task 7: Admin Category dan Product Management

**Files:**

- Create: `app/Livewire/Admin/Categories/CategoryIndex.php`
- Create: `app/Livewire/Admin/Products/ProductIndex.php`
- Create: `app/Livewire/Admin/Products/ProductForm.php`
- Create matching Blade views
- Modify: `routes/admin.php`
- Test: `tests/Feature/Admin/ProductManagementTest.php`

**Interfaces:**

- Consumes: catalog models from Tasks 5–6.
- Produces: category/product create, update, archive, activate, image upload.

- [ ] **Step 1: Tulis failing Livewire test**

```php
<?php

use App\Enums\SaleMode;
use App\Livewire\Admin\Products\ProductForm;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('creates a product with a variant', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create();

    Livewire::test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Kopi Susu')
        ->set('description', 'Kopi susu gula aren')
        ->set('saleMode', SaleMode::ReadyStock->value)
        ->set('variants', [[
            'name' => 'Regular',
            'sku' => 'KOPI-REG',
            'price' => 18000,
            'stock_on_hand' => 20,
            'is_active' => true,
        ]])
        ->call('save')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Kopi Susu')->first()->variants)->toHaveCount(1);
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Feature/Admin/ProductManagementTest.php
```

Expected: FAIL.

- [ ] **Step 3: Implementasikan validation dan transaction**

Product rules:

```php
[
    'categoryId' => ['required', 'exists:categories,id'],
    'name' => ['required', 'string', 'max:150'],
    'description' => ['nullable', 'string', 'max:5000'],
    'saleMode' => ['required', Rule::enum(SaleMode::class)],
    'variants' => ['required', 'array', 'min:1'],
    'variants.*.name' => ['required', 'string', 'max:100'],
    'variants.*.sku' => ['required', 'string', 'max:100', 'distinct'],
    'variants.*.price' => ['required', 'integer', 'min:0'],
    'variants.*.stock_on_hand' => ['required', 'integer', 'min:0'],
]
```

Save product and variants inside `DB::transaction`. Generate slug using `Str::slug` with a uniqueness suffix. Archive with soft delete; do not hard-delete.

- [ ] **Step 4: Tambahkan UI states**

Blade view must include:

- Inline validation.
- Dynamic variant rows.
- Loading/disabled state on save.
- Mobile stacked fields.
- Desktop two-column sections.
- Explicit labels and 44 px controls.

- [ ] **Step 5: Verifikasi dan commit**

```powershell
php artisan test tests/Feature/Admin/ProductManagementTest.php
git add app/Livewire resources/views/livewire/admin routes/admin.php tests/Feature/Admin
git commit -m "feat: add admin catalog management"
```

## Task 8: Inventory Adjustment

**Files:**

- Create migration `create_stock_movements_table`
- Create: `app/Models/StockMovement.php`
- Create: `app/Actions/Inventory/AdjustStock.php`
- Create: `app/Livewire/Admin/InventoryPage.php`
- Test: `tests/Feature/Admin/InventoryAdjustmentTest.php`

**Interfaces:**

- Produces: `AdjustStock::handle(ProductVariant $variant, int $delta, string $reason, User $actor): StockMovement`.

- [ ] **Step 1: Tulis failing test**

```php
<?php

use App\Actions\Inventory\AdjustStock;
use App\Models\ProductVariant;
use App\Models\User;

it('records stock adjustment and rejects negative stock', function () {
    $variant = ProductVariant::factory()->create(['stock_on_hand' => 5]);
    $admin = User::factory()->create();

    app(AdjustStock::class)->handle($variant, 3, 'Restock', $admin);

    expect($variant->fresh()->stock_on_hand)->toBe(8)
        ->and($variant->stockMovements()->count())->toBe(1);

    app(AdjustStock::class)->handle($variant->fresh(), -9, 'Invalid', $admin);
})->throws(DomainException::class);
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Feature/Admin/InventoryAdjustmentTest.php
```

Expected: FAIL.

- [ ] **Step 3: Implementasikan atomic adjustment**

```php
public function handle(
    ProductVariant $variant,
    int $delta,
    string $reason,
    User $actor,
): StockMovement {
    return DB::transaction(function () use ($variant, $delta, $reason, $actor) {
        $locked = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
        $before = $locked->stock_on_hand;
        $after = $before + $delta;

        if ($after < $locked->reserved_stock || $after < 0) {
            throw new DomainException('Stock cannot be lower than reserved stock.');
        }

        $locked->update(['stock_on_hand' => $after]);

        return $locked->stockMovements()->create([
            'type' => $delta >= 0 ? 'adjustment_in' : 'adjustment_out',
            'quantity_delta' => $delta,
            'stock_before' => $before,
            'stock_after' => $after,
            'note' => $reason,
            'created_by' => $actor->id,
        ]);
    });
}
```

- [ ] **Step 4: Verifikasi dan commit**

```powershell
php artisan test tests/Feature/Admin/InventoryAdjustmentTest.php
git add app database resources tests
git commit -m "feat: add audited stock adjustments"
```

## Task 9: Storefront Catalog

**Files:**

- Create: `app/Livewire/Storefront/CatalogPage.php`
- Create: `resources/views/livewire/storefront/catalog-page.blade.php`
- Create: `resources/views/components/storefront/product-card.blade.php`
- Create: `resources/views/layouts/storefront.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Storefront/CatalogPageTest.php`

**Interfaces:**

- Produces: public catalog with `search`, `category`, `saleMode`, pagination.

- [ ] **Step 1: Tulis failing catalog test**

```php
<?php

use App\Livewire\Storefront\CatalogPage;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

it('shows only active products and filters by search', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create(['name' => 'Kopi Susu', 'is_active' => true]);
    Product::factory()->for($category)->create(['name' => 'Teh Lemon', 'is_active' => false]);

    Livewire::test(CatalogPage::class)
        ->set('search', 'Kopi')
        ->assertSee('Kopi Susu')
        ->assertDontSee('Teh Lemon');
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Feature/Storefront/CatalogPageTest.php
```

Expected: FAIL.

- [ ] **Step 3: Implementasikan query**

Use `WithPagination`, reset page when filters change, and eager load:

```php
Product::query()
    ->where('is_active', true)
    ->whereHas('category', fn ($query) => $query->where('is_active', true))
    ->with(['category', 'images', 'variants'])
    ->when($this->search !== '', fn ($query) =>
        $query->where('name', 'like', '%'.$this->search.'%'))
    ->when($this->category, fn ($query) =>
        $query->where('category_id', $this->category))
    ->when($this->saleMode, fn ($query) =>
        $query->whereIn('sale_mode', [$this->saleMode, SaleMode::Both->value]))
    ->orderByDesc('is_featured')
    ->orderBy('name')
    ->paginate(12);
```

- [ ] **Step 4: Implementasikan responsive layout**

Mobile:

- Search full-width.
- Category chips horizontal.
- Product grid one/two columns.
- Bottom navigation.

Desktop:

- Horizontal header.
- Search and filters visible.
- 3–4 column product grid.
- Hover/focus card states.

- [ ] **Step 5: Verifikasi dan commit**

```powershell
php artisan test tests/Feature/Storefront/CatalogPageTest.php
npm run build
git add app/Livewire resources routes tests/Feature/Storefront
git commit -m "feat: add responsive storefront catalog"
```

## Task 10: Product Detail dan Configuration

**Files:**

- Create: `app/Livewire/Storefront/ProductDetailPage.php`
- Create: `app/Services/Catalog/ProductConfigurationValidator.php`
- Create: `resources/views/livewire/storefront/product-detail-page.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Storefront/ProductDetailPageTest.php`
- Test: `tests/Unit/Catalog/ProductConfigurationValidatorTest.php`

**Interfaces:**

- Produces: valid selected variant/options/add-ons and estimated line price.

- [ ] **Step 1: Tulis failing validator test**

```php
<?php

use App\Services\Catalog\ProductConfigurationValidator;
use App\Models\Product;
use App\Models\ProductVariant;

it('rejects a missing required variant and calculates valid configuration', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create([
        'price' => 20000,
        'stock_on_hand' => 5,
    ]);

    $validator = app(ProductConfigurationValidator::class);

    expect(fn () => $validator->validate($product, null, [], [], 1))
        ->toThrow(DomainException::class);

    $result = $validator->validate($product, $variant->id, [], [], 2);

    expect($result->unitPrice)->toBe(20000)
        ->and($result->lineTotal)->toBe(40000);
});
```

- [ ] **Step 2: Jalankan test**

```powershell
php artisan test tests/Unit/Catalog/ProductConfigurationValidatorTest.php
```

Expected: FAIL.

- [ ] **Step 3: Implementasikan validation**

Validator must:

- Confirm product and variant are active.
- Confirm variant belongs to product.
- Confirm quantity is 1–99 and not above available stock.
- Confirm selected options belong to assigned groups.
- Enforce required/min/max selection.
- Confirm selected add-ons are assigned and active.
- Calculate unit price from variant, option price deltas, and add-ons.

Return immutable data:

```php
final readonly class ValidatedProductConfiguration
{
    public function __construct(
        public int $unitPrice,
        public int $lineTotal,
        public array $optionValueIds,
        public array $addonIds,
    ) {}
}
```

- [ ] **Step 4: Implementasikan page**

Mobile:

- Swipeable gallery.
- Stacked option groups.
- Sticky add-to-cart preview button; in M1 button verifies configuration and announces that cart arrives in M2.

Desktop:

- Gallery left and sticky configuration panel right.
- Hover thumbnails and focus-visible state.

- [ ] **Step 5: Verifikasi dan commit**

```powershell
php artisan test tests/Unit/Catalog/ProductConfigurationValidatorTest.php tests/Feature/Storefront/ProductDetailPageTest.php
npm run build
git add app resources routes tests
git commit -m "feat: add configurable product details"
```

## Task 11: Responsive and Accessibility Verification

**Files:**

- Modify: `resources/css/app.css`
- Modify: storefront/admin Blade views touched in M1
- Create: `tests/Feature/Storefront/AccessibilityMarkupTest.php`
- Create: `docs/testing/m1-responsive-checklist.md`

**Interfaces:**

- Produces: documented viewport and keyboard verification.

- [ ] **Step 1: Tulis markup test**

```php
<?php

it('renders Indonesian language and viewport metadata', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('lang="id"', false)
        ->assertSee('width=device-width, initial-scale=1', false);
});
```

- [ ] **Step 2: Jalankan automated checks**

```powershell
php artisan test tests/Feature/Storefront
npm run build
```

Expected: PASS.

- [ ] **Step 3: Jalankan manual viewport checklist**

Check at 360, 390, 430, 1280, and 1440 px:

```markdown
- [ ] No storefront horizontal overflow
- [ ] Touch controls are at least 44 px
- [ ] Mobile uses bottom navigation
- [ ] Desktop uses horizontal navigation
- [ ] Desktop catalog uses 3–4 columns
- [ ] Product detail changes from stacked to two-column
- [ ] Hover and focus states are visible
- [ ] All hover actions also work by click/keyboard
- [ ] Validation remains adjacent to fields
```

- [ ] **Step 4: Commit**

```powershell
git add resources tests docs/testing
git commit -m "test: verify milestone one responsive experience"
```

## Task 12: Milestone Verification dan Setup Guide

**Files:**

- Create: `README.md`
- Create: `docs/setup/local-development.md`
- Create: `database/seeders/DemoCatalogSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

**Interfaces:**

- Produces: reproducible local setup and demo data.

- [ ] **Step 1: Tambahkan demo seeder**

Seed:

- Satu admin.
- Satu store.
- Minimal tiga kategori.
- Minimal delapan produk.
- Ready stock, pre-order, dan both.
- Minimal dua produk dengan varian/options/add-ons.
- Satu varian habis untuk empty/disabled state.

- [ ] **Step 2: Tulis setup guide**

Document exact commands:

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan test
```

For development:

```powershell
npm run dev
php artisan serve
```

Also document Laragon virtual host usage and required PHP/MySQL versions.

- [ ] **Step 3: Final verification**

```powershell
php artisan migrate:fresh --seed
php artisan test
npm run build
git status --short
```

Expected:

- Migration and seed complete.
- All tests PASS.
- Vite build exits 0.
- Only intended documentation changes remain before commit.

- [ ] **Step 4: Commit**

```powershell
git add README.md docs database
git commit -m "docs: add milestone one setup and demo data"
```

## Milestone 1 Review Gate

Do not begin Milestone 2 until:

- Admin login and protected routes work.
- Store settings persist.
- Catalog models and relationships pass.
- Product CRUD and archive work.
- Stock adjustment is audited and cannot go negative.
- Public catalog filters active products.
- Product detail validates configuration.
- Mobile and desktop checklists pass.
- Full test suite and asset build pass.
