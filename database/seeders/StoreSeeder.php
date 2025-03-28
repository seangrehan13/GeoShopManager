<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::create([
            'name' => 'Central Store',
            'coordinates' => new Point(51.509865, -0.118092), // Coordinates for London
            'status' => 'open',
            'store_type_id' => 'shop',
            'max_delivery_distance_in_meters' => 5000,
        ]);
    }
}
