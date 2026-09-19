<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_user_management_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);
    }

    public function test_cashier_is_redirected_away_from_user_management(): void
    {
        $cashier = User::where('role', 'cashier')->first();

        $this->actingAs($cashier)->get('/admin/users')->assertRedirect('/pos');
    }

    public function test_admin_can_create_a_cashier_account(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Cashier',
            'email' => 'cashier2@textilepos.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'cashier',
        ]);

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'name' => 'New Cashier',
            'email' => 'cashier2@textilepos.com',
            'role' => 'cashier',
        ]);

        $user = User::where('email', 'cashier2@textilepos.com')->first();
        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_admin_can_create_an_admin_account(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Second Admin',
            'email' => 'admin2@textilepos.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'admin',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'admin2@textilepos.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_update_an_account_role_and_password(): void
    {
        $admin = User::where('role', 'admin')->first();
        $cashier = User::where('role', 'cashier')->first();

        $this->actingAs($admin)->put("/admin/users/{$cashier->id}", [
            'name' => 'Renamed Cashier',
            'email' => 'updated@textilepos.com',
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
            'role' => 'cashier',
        ])->assertRedirect('/admin/users');

        $cashier->refresh();
        $this->assertEquals('Renamed Cashier', $cashier->name);
        $this->assertEquals('updated@textilepos.com', $cashier->email);
        $this->assertTrue(Hash::check('newsecret123', $cashier->password));
    }

    public function test_admin_can_update_account_without_changing_password(): void
    {
        $admin = User::where('role', 'admin')->first();
        $cashier = User::where('role', 'cashier')->first();
        $originalPassword = $cashier->password;

        $this->actingAs($admin)->put("/admin/users/{$cashier->id}", [
            'name' => 'Name Only Change',
            'email' => $cashier->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => 'cashier',
        ])->assertRedirect('/admin/users');

        $cashier->refresh();
        $this->assertEquals('Name Only Change', $cashier->name);
        $this->assertEquals($originalPassword, $cashier->password);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_cannot_delete_last_admin_account(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_cannot_delete_account_with_sales_history(): void
    {
        $admin = User::where('role', 'admin')->first();
        $cashier = User::where('role', 'cashier')->first();

        // Attach an order to the cashier
        Order::create([
            'invoice_number' => 'INV-TEST-001',
            'cashier_id' => $cashier->id,
            'total_amount' => 100.00,
            'paid_amount' => 100.00,
            'change_amount' => 0.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)->delete("/admin/users/{$cashier->id}")
            ->assertRedirect('/admin/users')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $cashier->id]);
    }

    public function test_admin_can_delete_cashier_without_sales_history(): void
    {
        $admin = User::where('role', 'admin')->first();

        $cashier = User::create([
            'name' => 'Temp Cashier',
            'email' => 'temp@textilepos.com',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
        ]);

        $this->actingAs($admin)->delete("/admin/users/{$cashier->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $cashier->id]);
    }

    public function test_created_account_can_login(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Fresh Cashier',
            'email' => 'fresh@textilepos.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'cashier',
        ]);

        $this->post('/login', [
            'email' => 'fresh@textilepos.com',
            'password' => 'secret123',
        ])->assertRedirect('/pos');

        $this->assertAuthenticated();
    }
}