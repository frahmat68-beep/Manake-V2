<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Category can have Equipments.
     */
    public function test_category_can_have_equipments(): void
    {
        $category = Category::factory()->create(['name' => 'Kamera']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sony FX3',
        ]);

        $this->assertCount(1, $category->equipments);
        $this->assertEquals('Sony FX3', $category->equipments->first()->name);
    }

    /**
     * Test Equipment is rentable when ready and stock is greater than 0.
     */
    public function test_equipment_is_rentable_when_ready_and_has_stock(): void
    {
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 3,
        ]);

        $this->assertTrue($equipment->isRentable());
        $this->assertTrue($equipment->isReady());
    }

    /**
     * Test Equipment is not rentable when stock is 0.
     */
    public function test_equipment_is_not_rentable_when_stock_is_zero(): void
    {
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 0,
        ]);

        $this->assertFalse($equipment->isRentable());
    }

    /**
     * Test Equipment is not rentable when in maintenance or unavailable.
     */
    public function test_equipment_is_not_rentable_when_not_ready(): void
    {
        $maint = Equipment::factory()->create([
            'status' => Equipment::STATUS_MAINTENANCE,
            'stock' => 5,
        ]);

        $unavail = Equipment::factory()->create([
            'status' => Equipment::STATUS_UNAVAILABLE,
            'stock' => 2,
        ]);

        $this->assertFalse($maint->isRentable());
        $this->assertFalse($maint->isReady());

        $this->assertFalse($unavail->isRentable());
        $this->assertFalse($unavail->isReady());
    }

    /**
     * Test order duration calculation is correct.
     */
    public function test_order_duration_calculation_is_correct(): void
    {
        // 2026-05-26 to 2026-05-26 is 1 day (inclusive)
        $this->assertEquals(1, Order::calculateDurationDays('2026-05-26', '2026-05-26'));

        // 2026-05-26 to 2026-05-28 is 3 days
        $this->assertEquals(3, Order::calculateDurationDays('2026-05-26', '2026-05-28'));
    }

    /**
     * Test seeder runs successfully and does not duplicate categories or equipments.
     */
    public function test_seeder_runs_successfully_and_is_idempotent(): void
    {
        // Run first time
        $this->seed(DatabaseSeeder::class);

        $initialCategoryCount = Category::count();
        $initialEquipmentCount = Equipment::count();

        $this->assertGreaterThan(0, $initialCategoryCount);
        $this->assertGreaterThan(0, $initialEquipmentCount);

        // Run second time to test safety
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals($initialCategoryCount, Category::count());
        $this->assertEquals($initialEquipmentCount, Equipment::count());
    }
}
