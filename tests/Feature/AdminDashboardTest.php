<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $category;
    protected $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create standard customer user
        $this->user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        // 2. Create administrative user
        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        // 3. Setup default Category and Equipment
        $this->category = Category::factory()->create([
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $this->equipment = Equipment::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Sony A7S III',
            'slug' => 'sony-a7s-iii',
            'stock' => 5,
            'price_per_day' => 600000,
            'status' => 'ready',
        ]);
    }

    /**
     * 1. Normal user cannot access admin dashboard.
     */
    public function test_normal_user_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * 2. Admin can access admin dashboard.
     */
    public function test_admin_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ADMIN DASHBOARD');
    }

    /**
     * 3. Admin can create category.
     */
    public function test_admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Lighting Pro',
                'slug' => 'lighting-pro',
                'description' => 'Continuous video lighting.',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Lighting Pro',
            'slug' => 'lighting-pro',
        ]);
    }

    /**
     * 4. Admin can update category.
     */
    public function test_admin_can_update_category()
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.categories.update', $this->category->id), [
                'name' => 'Kamera Cinema',
                'slug' => 'kamera-cinema',
                'description' => 'Cinema line mirrorless.',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => 'Kamera Cinema',
            'slug' => 'kamera-cinema',
        ]);
    }

    /**
     * 5. Admin can create equipment.
     */
    public function test_admin_can_create_equipment()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.equipments.store'), [
                'category_id' => $this->category->id,
                'name' => 'DJI Mavic 3 Cine',
                'slug' => 'dji-mavic-3-cine',
                'stock' => 2,
                'price_per_day' => 950000,
                'status' => 'ready',
                'description' => 'Aerial cine camera.',
            ]);

        $response->assertRedirect(route('admin.equipments.index'));
        $this->assertDatabaseHas('equipments', [
            'name' => 'DJI Mavic 3 Cine',
            'slug' => 'dji-mavic-3-cine',
            'price_per_day' => 950000,
        ]);
    }

    /**
     * 6. Admin can update equipment status to maintenance.
     */
    public function test_admin_can_update_equipment_status_to_maintenance()
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.equipments.update', $this->equipment->id), [
                'category_id' => $this->category->id,
                'name' => $this->equipment->name,
                'slug' => $this->equipment->slug,
                'stock' => $this->equipment->stock,
                'price_per_day' => $this->equipment->price_per_day,
                'status' => 'maintenance',
            ]);

        $response->assertRedirect(route('admin.equipments.index'));
        $this->assertDatabaseHas('equipments', [
            'id' => $this->equipment->id,
            'status' => 'maintenance',
        ]);
    }

    /**
     * 7. Admin can view orders.
     */
    public function test_admin_can_view_orders()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-777',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 1200000,
            'tax_rate' => 11.00,
            'tax_amount' => 132000,
            'total_amount' => 1332000,
            'grand_total' => 1332000,
            'payment_status' => 'pending',
            'rental_status' => 'waiting_payment',
            'midtrans_order_id' => 'mid-777',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('MNK-777');
    }

    /**
     * 8. Admin can update rental status.
     */
    public function test_admin_can_update_rental_status()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-777',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 1200000,
            'tax_rate' => 11.00,
            'tax_amount' => 132000,
            'total_amount' => 1332000,
            'grand_total' => 1332000,
            'payment_status' => 'pending',
            'rental_status' => 'waiting_payment',
            'midtrans_order_id' => 'mid-777',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.orders.update-status', $order->id), [
                'status' => 'picked_up',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'rental_status' => 'picked_up',
        ]);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'from_status' => 'waiting_payment',
            'to_status' => 'picked_up',
            'actor_type' => 'admin',
        ]);
    }

    /**
     * 9. Admin can add additional fee.
     */
    public function test_admin_can_add_additional_fee()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-777',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 1200000,
            'tax_rate' => 11.00,
            'tax_amount' => 132000,
            'total_amount' => 1332000,
            'grand_total' => 1332000,
            'payment_status' => 'pending',
            'rental_status' => 'waiting_payment',
            'midtrans_order_id' => 'mid-777',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.orders.fees', $order->id), [
                'fee_type' => 'late',
                'amount' => 150000,
                'note' => 'Terlambat mengembalikan alat 1 hari.',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'additional_fee' => 150000,
            'grand_total' => 1482000, // 1332000 + 150000
        ]);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'additional_fee' => 150000,
            'note' => 'Penambahan biaya Denda Keterlambatan: Rp 150.000. Catatan: Terlambat mengembalikan alat 1 hari.',
        ]);
    }

    /**
     * 10. Admin can view payments.
     */
    public function test_admin_can_view_payments()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-777',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 1200000,
            'tax_rate' => 11.00,
            'tax_amount' => 132000,
            'total_amount' => 1332000,
            'grand_total' => 1332000,
            'payment_status' => 'pending',
            'rental_status' => 'waiting_payment',
            'midtrans_order_id' => 'mid-777',
            'expired_at' => now()->addHours(24),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => 'mid-777',
            'snap_token' => 'snap-777',
            'status' => 'pending',
            'gross_amount' => 1332000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('mid-777');
    }

    /**
     * 11. Admin link visible only to admin.
     */
    public function test_admin_link_visible_only_to_admin()
    {
        // 1. Regular user should NOT see Admin Panel link on homepage
        $response = $this->actingAs($this->user)
            ->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Admin Panel');

        // 2. Admin user should see Admin Panel link on homepage
        $response = $this->actingAs($this->admin)
            ->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Admin Panel');
    }

    /**
     * 12. Admin can create equipment with valid JSON specifications.
     */
    public function test_admin_can_create_equipment_with_json_specifications()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.equipments.store'), [
                'category_id'  => $this->category->id,
                'name'         => 'Sony FX6',
                'slug'         => 'sony-fx6',
                'stock'        => 2,
                'price_per_day'=> 800000,
                'status'       => 'ready',
                'specifications'=> '{"sensor":"Full Frame","recording":"4K 120fps"}',
            ]);

        $response->assertRedirect(route('admin.equipments.index'));

        $eq = Equipment::where('slug', 'sony-fx6')->firstOrFail();
        $this->assertIsArray($eq->specifications);
        $this->assertEquals('Full Frame', $eq->specifications['sensor']);
        $this->assertEquals('4K 120fps', $eq->specifications['recording']);
    }

    /**
     * 13. Admin can create equipment with plain text specifications (auto-wrapped as notes).
     */
    public function test_admin_can_create_equipment_with_plain_text_specifications()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.equipments.store'), [
                'category_id'   => $this->category->id,
                'name'          => 'Rode NTG5',
                'slug'          => 'rode-ntg5',
                'stock'         => 3,
                'price_per_day' => 250000,
                'status'        => 'ready',
                'specifications'=> 'Shotgun microphone, frequency 20Hz-20kHz, weight 76g',
            ]);

        $response->assertRedirect(route('admin.equipments.index'));

        $eq = Equipment::where('slug', 'rode-ntg5')->firstOrFail();
        $this->assertIsArray($eq->specifications);
        $this->assertArrayHasKey('notes', $eq->specifications);
        $this->assertStringContainsString('Shotgun microphone', $eq->specifications['notes']);
    }

    /**
     * 14. Admin can update equipment specifications from JSON to plain text.
     */
    public function test_admin_can_update_specifications_from_json_to_plain_text()
    {
        // Start with JSON specs
        $this->equipment->update([
            'specifications' => ['sensor' => 'APS-C', 'mount' => 'E-Mount'],
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.equipments.update', $this->equipment->id), [
                'category_id'   => $this->category->id,
                'name'          => $this->equipment->name,
                'slug'          => $this->equipment->slug,
                'stock'         => $this->equipment->stock,
                'price_per_day' => $this->equipment->price_per_day,
                'status'        => $this->equipment->status,
                'specifications'=> 'Kamera mirrorless Sony dengan sensor APS-C, mount E-Mount.',
            ]);

        $response->assertRedirect(route('admin.equipments.index'));

        $this->equipment->refresh();
        $this->assertIsArray($this->equipment->specifications);
        $this->assertArrayHasKey('notes', $this->equipment->specifications);
        $this->assertStringContainsString('APS-C', $this->equipment->specifications['notes']);
    }

    /**
     * 15. Public product detail renders equipment with plain text (notes) specifications.
     */
    public function test_public_product_detail_renders_notes_specifications()
    {
        $this->equipment->update([
            'specifications' => ['notes' => 'Kamera full frame 12 megapixel untuk video produksi.'],
        ]);

        $response = $this->get(route('product.show', $this->equipment->slug));

        $response->assertStatus(200);
        $response->assertSee('Spesifikasi Teknis');
        $response->assertSee('Kamera full frame 12 megapixel untuk video produksi.');
    }

    /**
     * 16. Public product detail renders equipment with key/value JSON specifications.
     */
    public function test_public_product_detail_renders_key_value_specifications()
    {
        $this->equipment->update([
            'specifications' => [
                'sensor'    => 'Full Frame BSI CMOS',
                'recording' => '4K 120fps 10-bit',
                'mount'     => 'Sony E-Mount',
            ],
        ]);

        $response = $this->get(route('product.show', $this->equipment->slug));

        $response->assertStatus(200);
        $response->assertSee('Spesifikasi Teknis');
        $response->assertSee('Full Frame BSI CMOS');
        $response->assertSee('4K 120fps 10-bit');
    }
}
