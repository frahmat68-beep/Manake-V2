<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'specifications' => [
                'Resolusi' => '4K UHD',
                'Sensor' => 'Full Frame',
            ],
            'stock' => $this->faker->numberBetween(1, 10),
            'price_per_day' => $this->faker->numberBetween(150000, 750000),
            'status' => Equipment::STATUS_READY,
            'image_path' => null,
        ];
    }
}
