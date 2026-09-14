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