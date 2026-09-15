<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_and_update_shop_settings(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Access settings page
        $this->actingAs($admin)->get('/admin/settings')->assertStatus(200);

        // Update settings
        $response = $this->actingAs($admin)->post('/admin/settings', [
            'shop_name' => 'ROYAL TEXTILE & FABRICS',
            'shop_address' => '456 Galleria Mall, Kandy',
            'phone_number' => '+94 81 999 8888',
            'currency_symbol' => '$',
            'receipt_footer' => 'No refunds. Exchanges within 14 days.',
        ]);

        $response->assertRedirect('/admin/settings');

        // Check settings in DB
        $this->assertDatabaseHas('settings', [
            'key' => 'shop_name',
            'value' => 'ROYAL TEXTILE & FABRICS',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'currency_symbol',
            'value' => '$',
        ]);
    }

    public function test_thermal_receipt_renders_dynamic_shop_settings_and_currency(): void
    {
        $admin = User::where('role', 'admin')->first();
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::first();

        // Update shop settings first with custom currency
        $postRes = $this->actingAs($admin)->post('/admin/settings', [
            'shop_name' => 'TEXTILE KINGDOM',
            'shop_address' => '789 Main Street, Galle',
            'phone_number' => '+94 91 555 1234',
            'currency_symbol' => 'AED',
            'receipt_footer' => 'Thank you for choosing Textile Kingdom!',
        ]);
        $postRes->assertSessionHasNoErrors();
        $postRes->assertRedirect('/admin/settings');

        // Create an order
        $order = Order::create([
            'invoice_number' => 'INV-20260915-TEST1',
            'cashier_id' => $cashier->id,
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00,
            'change_amount' => 0.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'item_code' => $product->item_code,
            'unit_price' => 1000.00,
            'quantity' => 1,
            'subtotal' => 1000.00,
        ]);

        // Access receipt view
        $receiptResponse = $this->actingAs($cashier)->get("/orders/{$order->id}/receipt");

        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('TEXTILE KINGDOM');
        $receiptResponse->assertSee('+94 91 555 1234');
        $receiptResponse->assertSee('AED 1,000.00');
        $receiptResponse->assertSee('Thank you for choosing Textile Kingdom!');
    }
}
