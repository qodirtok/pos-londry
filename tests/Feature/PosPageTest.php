<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\ProductionSeeder;
use App\Models\User;

class PosPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductionSeeder::class);
    }

    public function test_pos_requires_login(): void
    {
        $this->get('/pos')->assertRedirect('/login');
    }

    public function test_home_redirects_away_when_unauthenticated(): void
    {
        // Route / (dashboard) redirects to login when not authenticated
        $this->get('/')->assertRedirect();
    }

    public function test_pos_page_renders_mobile_cart_drawer_elements(): void
    {
        $user = User::where('username', 'admin')->first();
        $this->assertNotNull($user);

        $this->actingAs($user)
            ->withoutVite()
            ->get('/pos')
            ->assertOk()
            ->assertSee('id="cartDrawer"', false)
            ->assertSee('id="cartFab"', false)
            ->assertSee('id="cartDrawerOverlay"', false)
            ->assertSee('id="cartCount"', false)
            ->assertSee('function toggleCartDrawer', false)
            ->assertSee('function updateCartUI', false)
            ->assertSee('cart-open', false);
    }

    public function test_pos_page_contains_cart_drawer_css_media_queries(): void
    {
        $user = User::where('username', 'admin')->first();

        $html = $this->actingAs($user)
            ->withoutVite()
            ->get('/pos')
            ->getContent();

        $this->assertStringContainsString('@media(max-width:1023.5px)', $html);
        $this->assertStringContainsString('@media(min-width:1024px)', $html);
        $this->assertStringContainsString('#cartDrawer.cart-open{transform:translateY(0)}', $html);
        $this->assertStringContainsString('#cartFab.hidden{display:none}', $html);
        // ID selector mobile style harus tampil SEBELUM reset desktop (urutan cascade)
        $this->assertLessThan(
            strpos($html, '@media(min-width:1024px)'),
            strpos($html, '@media(max-width:1023.5px)')
        );
    }

    /**
     * Modal laundry pernah terpotong di ponsel sempit karena footer memakai
     * grid 1fr 1fr dan nama jenis laundry dipaksa satu baris. Pengukuran
     * headless Chrome di 320 sampai 768 px menunjukkan keduanya sudah rapi
     * karena footer jadi satu kolom di bawah 640px dan kartu laundry wrap
     * di bawah 420px.
     *
     * Guard ini mengunci aturan itu di level CSS. Menghapus salah satu
     * media query akan mengembalikan pemotongan tapi test lain tetap hijau,
     * karena tidak ada assertion yang mengukur lebar di layar sempit.
     */
    public function test_laundry_modal_keeps_narrow_screen_layout_rules(): void
    {
        $user = User::where('username', 'admin')->first();
        $this->assertNotNull($user);

        $html = $this->actingAs($user)
            ->withoutVite()
            ->get('/pos')
            ->getContent();

        // Lebar modal dibatasi inline, jadi harus jauh di bawah viewport
        // terkecil yang masih dipakai kasir (320px) setelah dikurangi padding.
        $this->assertStringContainsString('id="modalLaundry"', $html);
        $this->assertStringContainsString('.pos-modal{background:#fff;width:100%;max-width:480px', $html);

        // Footer satu kolom di bawah 640px supaya label panjang
        // "Simpan dan Tutup" tidak terpotong.
        $this->assertMatchesRegularExpression(
            '/@media\(max-width:639\.98px\)\s*\{\s*\.laundry-footer\{grid-template-columns:1fr\}/',
            $html
        );
        $this->assertStringContainsString('.laundry-footer .btn{width:100%;padding:.9rem}', $html);

        // Nama jenis laundry dua baris di bawah 420px, dengan urutan flex
        // supaya ikon, nama, jumlah, dan hapus tidak saling tumpang tindih.
        $this->assertMatchesRegularExpression(
            '/@media\(max-width:420px\)\s*\{\s*\.pos-laundry-card\{flex-wrap:wrap\}/',
            $html
        );
        $this->assertStringContainsString('.pos-laundry-card .ll-name{flex:1 1 100%;order:1;white-space:normal;line-height:1.3}', $html);

        // Target sentuh minimal 2.5rem (40px) untuk tombol langkah, hapus,
        // dan input jumlah, karena kasir berdiri sambil memegang barang.
        foreach (['.pos-laundry-card .ll-step{width:2.5rem;height:2.5rem', '.pos-laundry-card .ll-remove{width:2.5rem;height:2.5rem', '.pos-laundry-card input.ll-input{width:3rem;height:2.5rem'] as $rule) {
            $this->assertStringContainsString($rule, $html, "Aturan target sentuh hilang: {$rule}");
        }
    }

    public function test_pos_store_creates_order_for_kasir(): void
    {
        $kasir = User::where('username', 'kasir')->first();
        $this->assertNotNull($kasir);

        $product = \App\Models\Product::where('type', 'product')->first();
        $customer = \App\Models\Customer::first();
        $this->assertNotNull($product);
        $this->assertNotNull($customer);

        $branches = $kasir->branches;
        $branchId = $branches->count() ? $branches->first()->id : $kasir->branch_id;

        $payload = [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'price' => (float)$product->price, 'discount' => 0],
            ],
            'paid_amount' => (float)$product->price * 2,
            'payment_method' => 'cash',
            'discount' => 0,
            'tax' => 0,
            'order_status' => 'received',
            'laundry_details' => [],
        ];

        $this->actingAs($kasir)
            ->postJson('/pos', $payload)
            ->assertOk()
            ->assertJsonPath('order_status', 'received');

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'order_status' => 'received',
        ]);
    }
}