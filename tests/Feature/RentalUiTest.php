<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalUiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test homepage loads successfully.
     */
    public function test_homepage_loads(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Sewa Alat Produksi Media');
        $response->assertSee('Lihat Katalog');
    }

    /**
     * Test catalog loads successfully.
     */
    public function test_catalog_loads(): void
    {
        $response = $this->get(route('catalog'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Peralatan Media');
    }

    /**
     * Test catalog can filter by category slug.
     */
    public function test_catalog_can_filter_by_category(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Kamera', 'slug' => 'kamera']);
        $cat2 = Category::factory()->create(['name' => 'Lighting', 'slug' => 'lighting']);

        $eq1 = Equipment::factory()->create(['category_id' => $cat1->id, 'name' => 'Sony FX3']);
        $eq2 = Equipment::factory()->create(['category_id' => $cat2->id, 'name' => 'Aputure 300D']);

        // Saring Kamera
        $response = $this->get(route('catalog', ['category' => 'kamera']));
        $response->assertStatus(200);
        $response->assertSee('Sony FX3');
        $response->assertDontSee('Aputure 300D');
    }

    /**
     * Test product detail loads successfully.
     */
    public function test_product_detail_loads(): void
    {
        $equipment = Equipment::factory()->create(['name' => 'Sony A7S III']);

        $response = $this->get(route('product.show', $equipment->slug));

        $response->assertStatus(200);
        $response->assertSee('Sony A7S III');
    }

    /**
     * Test guest product detail shows login CTA.
     */
    public function test_guest_product_detail_shows_login_cta(): void
    {
        $equipment = Equipment::factory()->create(['name' => 'Rode Wireless GO']);

        $response = $this->get(route('product.show', $equipment->slug));

        $response->assertStatus(200);
        $response->assertSee('Masuk untuk Menyewa');
    }

    /**
     * Test authenticated user can add available item to cart through form.
     */
    public function test_authenticated_user_can_add_available_item_to_cart_through_form(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'name' => 'DJI RS 3 Pro',
            'status' => Equipment::STATUS_READY,
            'stock' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'rental_start_date' => date('Y-m-d'),
            'rental_end_date' => date('Y-m-d', strtotime('+1 day')),
            'qty' => 1,
        ]);

        // Redirects to cart index page
        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
            'qty' => 1,
        ]);
    }

    /**
     * Test cart page loads for authenticated user.
     */
    public function test_cart_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('Keranjang Belanja');
    }

    /**
     * Test checkout preview page redirects to cart gracefully when empty.
     */
    public function test_checkout_preview_page_redirects_empty_cart_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('checkout.index'));

        // Since cart is empty, redirects to cart index page with errors
        $response->assertRedirect(route('cart.index'));
    }

    /**
     * Test checkout preview works with valid cart.
     */
    public function test_checkout_preview_works_with_valid_cart(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'name' => 'DJI Mavic 3',
            'status' => Equipment::STATUS_READY,
            'stock' => 5,
        ]);

        // Stage an item in the cart first
        $this->actingAs($user)->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'rental_start_date' => date('Y-m-d'),
            'rental_end_date' => date('Y-m-d', strtotime('+1 day')),
            'qty' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Preview Checkout');
        $response->assertSee('DJI Mavic 3');
        $response->assertSee('Buat Pesanan');
    }

    /**
     * Test availability endpoint still returns JSON.
     */
    public function test_availability_endpoint_still_returns_json(): void
    {
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 5,
        ]);

        $response = $this->get(route('product.availability', [
            'equipment' => $equipment->slug,
            'rental_start_date' => date('Y-m-d'),
            'rental_end_date' => date('Y-m-d', strtotime('+1 day')),
            'qty' => 1,
        ]), ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'ok', 'status', 'stock', 'available_units', 'message'
        ]);
    }
}
