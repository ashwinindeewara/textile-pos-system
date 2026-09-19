<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_cashier_login_redirects_to_pos_screen(): void
    {
        $response = $this->post('/login', [
            'email' => 'cashier@textilepos.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/pos');
        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@textilepos.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    public function test_item_code_lookup_returns_product_details(): void
    {
        $cashier = User::where('role', 'cashier')->first();

        $response = $this->actingAs($cashier)->postJson('/pos/lookup', [
            'code' => 'DNM-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'product' => [
                    'item_code' => 'DNM-001',
                    'name' => 'Slim Fit Dark Blue Denim Jeans',
                ]
            ]);
    }

    public function test_holding_an_order_parks_sale_without_deducting_stock(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'DNM-001')->first();
        $initialStock = $product->stock_qty;

        $response = $this->actingAs($cashier)->postJson('/pos/hold', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Stock quantity must NOT be deducted when held
        $product->refresh();
        $this->assertEquals($initialStock, $product->stock_qty);

        // Order saved with status = 'held'
        $this->assertDatabaseHas('orders', [
            'cashier_id' => $cashier->id,
            'status' => 'held',
        ]);
    }

    public function test_recalling_held_order_returns_items_and_removes_held_record(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'DNM-001')->first();

        // Create a held order first
        $holdResponse = $this->actingAs($cashier)->postJson('/pos/hold', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        $heldOrderId = $holdResponse->json('hold_id');

        // Recall held order
        $recallResponse = $this->actingAs($cashier)->postJson("/pos/recall/{$heldOrderId}");

        $recallResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(1, $recallResponse->json('items'));

        // Held order record should be deleted upon recall
        $this->assertDatabaseMissing('orders', [
            'id' => $heldOrderId,
        ]);
    }

    public function test_checkout_decreases_product_stock_qty_in_database(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'DNM-001')->first();

        $initialStock = $product->stock_qty; // e.g. 25
        $purchaseQty = 3;

        $response = $this->actingAs($cashier)->postJson('/pos/checkout', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => $purchaseQty,
                ]
            ],
            'paid_amount' => 15000.00,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check stock quantity was accurately decremented
        $product->refresh();
        $this->assertEquals($initialStock - $purchaseQty, $product->stock_qty);

        // Check Order was created in DB with status completed
        $this->assertDatabaseHas('orders', [
            'cashier_id' => $cashier->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => $purchaseQty,
        ]);
    }

    public function test_checkout_applies_percentage_discount_to_total(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'TSH-001')->first(); // price 1800.00

        $response = $this->actingAs($cashier)->postJson('/pos/checkout', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 5,
                ]
            ],
            'paid_amount' => 8100.00,
            'payment_method' => 'cash',
            'discount_percent' => 10,
            'discount_amount' => 900.00,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Subtotal 9000.00 - 10% (900.00) = 8100.00 grand total
        $this->assertDatabaseHas('orders', [
            'cashier_id' => $cashier->id,
            'status' => 'completed',
            'total_amount' => 8100.00,
            'discount_percent' => 10,
            'discount_amount' => 900.00,
            'change_amount' => 0.00,
        ]);
    }

    public function test_checkout_applies_fixed_amount_discount_to_total(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'TSH-001')->first(); // price 1800.00

        $response = $this->actingAs($cashier)->postJson('/pos/checkout', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 5,
                ]
            ],
            'paid_amount' => 8500.00,
            'payment_method' => 'cash',
            'discount_percent' => null,
            'discount_amount' => 500.00,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Subtotal 9000.00 - 500.00 = 8500.00 grand total
        $this->assertDatabaseHas('orders', [
            'cashier_id' => $cashier->id,
            'status' => 'completed',
            'total_amount' => 8500.00,
            'discount_percent' => null,
            'discount_amount' => 500.00,
            'change_amount' => 0.00,
        ]);
    }

    public function test_checkout_rejects_payment_below_discounted_total(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'TSH-001')->first(); // price 1800.00

        $response = $this->actingAs($cashier)->postJson('/pos/checkout', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 5,
                ]
            ],
            'paid_amount' => 8000.00, // below discounted 8100.00
            'payment_method' => 'cash',
            'discount_percent' => 10,
            'discount_amount' => 900.00,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_hold_order_stores_discount_and_recall_returns_it(): void
    {
        $cashier = User::where('role', 'cashier')->first();
        $product = Product::where('item_code', 'TSH-001')->first(); // price 1800.00

        $holdResponse = $this->actingAs($cashier)->postJson('/pos/hold', [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 3,
                ]
            ],
            'discount_percent' => 10,
            'discount_amount' => 540.00,
        ]);

        $holdResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'id' => $holdResponse->json('hold_id'),
            'total_amount' => 4860.00, // 5400.00 - 540.00
            'discount_percent' => 10,
            'discount_amount' => 540.00,
        ]);

        $recallResponse = $this->actingAs($cashier)->postJson("/pos/recall/{$holdResponse->json('hold_id')}");

        $recallResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'discount_percent' => 10,
                'discount_amount' => 540.00,
            ]);

        $this->assertCount(1, $recallResponse->json('items'));
    }
}
