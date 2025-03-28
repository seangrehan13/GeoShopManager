<?php

namespace Database\Seeders;

use App\Models\StoreType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'takeaway',
            'shop',
            'restaurant',
        ];

        foreach ($types as $type) {
            StoreType::updateOrCreate(['id' => $type]);
        }
    }
}
