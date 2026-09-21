<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SubscriptionLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function makeSuperAdmin(): User
    {
        return User::create([
            'name' => 'Vendor',
            'email' => 'vendor@provider.com',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
        ]);
    }

    public function test_hidden_vendor_login_page_is_accessible(): void
    {
        $this->get(route('superadmin.login'))->assertStatus(200);
    }

    public function test_super_admin_can_login_via_hidden_portal(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->post(route('superadmin.login.submit'), [
            'email' => $superAdmin->email,
            'password' => 'secret123',
        ])->assertRedirect(route('superadmin.dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_regular_user_cannot_login_via_hidden_portal(): void
    {
        $this->post(route('superadmin.login.submit'), [
            'email' => 'admin@textilepos.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_super_admin_dashboard_is_only_for_super_admins(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $admin = User::where('role', 'admin')->first();
        $cashier = User::where('role', 'cashier')->first();

        $this->actingAs($superAdmin)->get(route('superadmin.dashboard'))->assertStatus(200);

        // The vendor portal is hidden; store accounts must be rejected.
        $this->actingAs($admin)->get(route('superadmin.dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($cashier)->get(route('superadmin.dashboard'))->assertRedirect(route('pos.index'));
    }

    public function test_store_users_are_not_blocked_when_subscription_is_active(): void
    {
        SubscriptionService::setDueDate(now()->addDays(10)->toDateString());
        SubscriptionService::setGraceDays(5);

        $cashier = User::where('role', 'cashier')->first();

        $this->actingAs($cashier)->get(route('pos.index'))->assertOk();
    }

    public function test_manual_lock_blocks_store_users_but_not_super_admin(): void
    {
        SubscriptionService::setManuallyLocked(true);

        $cashier = User::where('role', 'cashier')->first();
        $admin = User::where('role', 'admin')->first();
        $superAdmin = $this->makeSuperAdmin();

        // Store users are escorted to the locked screen.
        $this->actingAs($cashier)->get(route('pos.index'))->assertRedirect(route('system.locked'));
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertRedirect(route('system.locked'));

        // Super admin stays fully accessible.
        $this->actingAs($superAdmin)->get(route('superadmin.dashboard'))->assertOk();

        // Locked screen renders for store users.
        $this->actingAs($cashier)->get(route('system.locked'))
            ->assertOk()
            ->assertSee('System Locked');

        // Unlock restores access.
        SubscriptionService::setManuallyLocked(false);
        $this->actingAs($cashier)->get(route('pos.index'))->assertOk();
    }

    public function test_json_requests_receive_lock_status_without_redirect(): void
    {
        SubscriptionService::setManuallyLocked(true);

        $cashier = User::where('role', 'cashier')->first();

        $this->actingAs($cashier)->postJson(route('pos.lookup'), ['code' => 'DNM-001'])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'locked' => true]);
    }

    public function test_system_auto_locks_after_due_date_plus_grace_days(): void
    {
        $cashier = User::where('role', 'cashier')->first();

        // Due yesterday with zero grace days -> lock date has passed.
        SubscriptionService::setDueDate(now()->subDay()->toDateString());
        SubscriptionService::setGraceDays(0);

        $this->assertTrue(SubscriptionService::isLocked());
        $this->assertEquals('overdue', SubscriptionService::lockReason());
        $this->actingAs($cashier)->get(route('pos.index'))->assertRedirect(route('system.locked'));

        // Still within the grace window -> not locked yet.
        SubscriptionService::setDueDate(now()->subDay()->toDateString());
        SubscriptionService::setGraceDays(3);

        $this->assertFalse(SubscriptionService::isLocked());
        $this->actingAs($cashier)->get(route('pos.index'))->assertOk();
    }

    public function test_super_admin_can_update_subscription_schedule(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $due = now()->addDays(20)->toDateString();

        $this->actingAs($superAdmin)->post(route('superadmin.subscription.update'), [
            'due_date' => $due,
            'grace_days' => 7,
        ])->assertRedirect(route('superadmin.dashboard'));

        $this->assertSame($due, optional(SubscriptionService::dueDate())->toDateString());
        $this->assertSame(7, SubscriptionService::graceDays());
    }

    public function test_super_admin_can_toggle_manual_lock(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->actingAs($superAdmin)->post(route('superadmin.lock.toggle'), ['locked' => '1'])
            ->assertRedirect(route('superadmin.dashboard'));

        $this->assertTrue(SubscriptionService::isManuallyLocked());
        $this->assertTrue(SubscriptionService::isLocked());

        $this->actingAs($superAdmin)->post(route('superadmin.lock.toggle'), ['locked' => '0'])
            ->assertRedirect(route('superadmin.dashboard'));

        $this->assertFalse(SubscriptionService::isManuallyLocked());
        $this->assertFalse(SubscriptionService::isLocked());
    }

    public function test_super_admin_is_hidden_from_store_user_list(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->makeSuperAdmin();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertDontSee('vendor@provider.com');
    }
}
