<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Demo User
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // 2. Seed Categories
        $categoriesData = [
            ['name' => 'Kamera', 'description' => 'Professional Cinema and Mirrorless Cameras'],
            ['name' => 'Lensa', 'description' => 'Prime and Zoom Lenses'],
            ['name' => 'Lighting', 'description' => 'Continuous and Strobe Lights'],
            ['name' => 'Audio', 'description' => 'Microphones and Recorders'],
            ['name' => 'Handy Talky', 'description' => 'Communication Transceivers'],
            ['name' => 'Drone', 'description' => 'Aerial Photography Equipment'],
            ['name' => 'Stabilizer', 'description' => 'Gimbals and Camera Rigs'],
            ['name' => 'Monitor Wireless', 'description' => 'Wireless Video Transmitters and Monitors'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $slug = Str::slug($cat['name']);
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }

        // 3. Seed Equipment Items
        $equipmentsData = [
            [
                'category_slug' => 'kamera',
                'name' => 'Sony FX3 Cinema Line',
                'description' => 'Sony Cinema Line full-frame camera with outstanding low-light sensitivity.',
                'specifications' => ['Sensor' => 'Full Frame', 'Resolusi' => '4K 120p', 'ISO' => 'Dual Base ISO'],
                'stock' => 3,
                'price_per_day' => 850000,
            ],
            [
                'category_slug' => 'kamera',
                'name' => 'Sony A7S III',
                'description' => 'Highly capable mirrorless camera optimized for video recording.',
                'specifications' => ['Sensor' => 'Full Frame', 'Resolusi' => '4K 120p', 'S-Log' => 'S-Log3'],
                'stock' => 5,
                'price_per_day' => 600000,
            ],
            [
                'category_slug' => 'kamera',
                'name' => 'Canon EOS R5',
                'description' => 'High-resolution professional mirrorless camera supporting 8K capture.',
                'specifications' => ['Sensor' => 'Full Frame', 'Resolusi' => '8K RAW', 'Megapiksel' => '45MP'],
                'stock' => 2,
                'price_per_day' => 700000,
            ],
            [
                'category_slug' => 'lensa',
                'name' => 'Sigma 24-70mm f/2.8',
                'description' => 'Versatile standard zoom lens with wide f/2.8 aperture.',
                'specifications' => ['Aperture' => 'f/2.8', 'Focal Length' => '24-70mm', 'Mount' => 'Sony E-Mount'],
                'stock' => 8,
                'price_per_day' => 250000,
            ],
            [
                'category_slug' => 'lighting',
                'name' => 'Aputure 300D II',
                'description' => 'Powerful continuous COB LED light suitable for video production.',
                'specifications' => ['Daya' => '300W', 'Temperatur' => '5600K', 'CRI' => '96+'],
                'stock' => 4,
                'price_per_day' => 350000,
            ],
            [
                'category_slug' => 'audio',
                'name' => 'Rode Wireless GO II',
                'description' => 'Compact and dual-channel wireless microphone system.',
                'specifications' => ['Saluran' => 'Dual Channel', 'Transmisi' => '2.4GHz', 'Jangkauan' => '200m'],
                'stock' => 10,
                'price_per_day' => 150000,
            ],
            [
                'category_slug' => 'handy-talky',
                'name' => 'Motorola HT Set',
                'description' => 'Reliable two-way radio set for on-set communication.',
                'specifications' => ['Rentang' => 'UHF/VHF', 'Paket' => 'Set of 4', 'Daya Tahan' => 'IP54'],
                'stock' => 12,
                'price_per_day' => 100000,
            ],
            [
                'category_slug' => 'stabilizer',
                'name' => 'DJI RS 3 Pro',
                'description' => 'Professional 3-axis camera gimbal with advanced stabilization.',
                'specifications' => ['Beban Maks' => '4.5 kg', 'Layar' => 'OLED Touchscreen', 'Fitur' => 'Auto-Locking'],
                'stock' => 4,
                'price_per_day' => 300000,
            ],
            [
                'category_slug' => 'drone',
                'name' => 'DJI Mavic 3 Cine',
                'description' => 'Flagship camera drone supporting Apple ProRes 422 HQ recording.',
                'specifications' => ['Kamera' => 'Hasselblad 4/3 CMOS', 'Prosesor' => 'ProRes 422 HQ', 'Terbang' => '46 Menit'],
                'stock' => 2,
                'price_per_day' => 950000,
            ],
        ];

        foreach ($equipmentsData as $item) {
            $slug = Str::slug($item['name']);
            $catId = isset($categories[$item['category_slug']]) ? $categories[$item['category_slug']]->id : null;

            Equipment::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $catId,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'specifications' => $item['specifications'],
                    'stock' => $item['stock'],
                    'price_per_day' => $item['price_per_day'],
                    'status' => Equipment::STATUS_READY,
                ]
            );
        }
    }
}
