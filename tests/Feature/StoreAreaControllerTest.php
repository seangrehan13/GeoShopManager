<?php

use App\Models\Postcode;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
 
it('returns stores within the default radius of 1000 meters', function () {
    $postcode = Postcode::factory()->create([
        'id' => 'AB101AB',
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.1495826, -2.1381223),
    ]);

    $nearestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149443, -2.125117),
        'max_delivery_distance_in_meters' => 1500,
    ]);

    $furthestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149521, -2.122631),
        'max_delivery_distance_in_meters' => 1000,
    ]);

    $response = $this->getJson('/api/stores/near/AB101AB');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'stores',
        ])
        ->assertJson([
            'message' => 'Stores near the postcode retrieved successfully.',
            'stores' => [
                [
                    'id' => $nearestStore->id,
                    'name' => $nearestStore->name,
                    'coordinates' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $nearestStore->coordinates->longitude,
                            $nearestStore->coordinates->latitude,
                        ],
                    ],
                    'status' => $nearestStore->status->value,
                    'type' => $nearestStore->store_type_id,
                    'max_delivery_distance_in_meters' => $nearestStore->max_delivery_distance_in_meters,
                    'distance' => 784.5995624951239,           
                ],
                [
                    'id' => $furthestStore->id,
                    'name' => $furthestStore->name,
                    'coordinates' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $furthestStore->coordinates->longitude,
                            $furthestStore->coordinates->latitude,
                        ],
                    ],
                    'status' => $furthestStore->status->value,
                    'type' => $furthestStore->store_type_id,
                    'max_delivery_distance_in_meters' => $furthestStore->max_delivery_distance_in_meters,
                    'distance' => 934.4191908591332,
                ],
            ],
        ]);
});

it('returns stores within a custom radius of 2000 meters', function () {
    $postcode = Postcode::factory()->create([
        'id' => 'AB101AB',
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.1495826, -2.1381223),
    ]);

    $nearestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149443, -2.115117),
        'max_delivery_distance_in_meters' => 1500,
    ]);

    $furthestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149521, -2.102631),
        'max_delivery_distance_in_meters' => 1000,
    ]);

    $response = $this->getJson('/api/stores/near/AB101AB?radius_in_meters=2000');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Stores near the postcode retrieved successfully.',
            'stores' => [
                [
                    'id' => $nearestStore->id,
                    'name' => $nearestStore->name,
                    'coordinates' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $nearestStore->coordinates->longitude,
                            $nearestStore->coordinates->latitude,
                        ],
                    ],
                    'status' => $nearestStore->status->value,
                    'type' => $nearestStore->store_type_id,
                    'max_delivery_distance_in_meters' => $nearestStore->max_delivery_distance_in_meters,
                    'distance' => 1387.7068957148635,           
                ],
            ],
        ]);
});

it('returns an empty list if no stores are within the radius', function () {
    $postcode = Postcode::factory()->create([
        'id' => 'AB101AB',
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.135, 2.117),
    ]);

    Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.200, 2.200),
        'max_delivery_distance_in_meters' => 500,
    ]);

    $response = $this->getJson('/api/stores/near/AB101AB?radius_in_meters=1000');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Stores near the postcode retrieved successfully.',
            'stores' => [],
        ]);
});

it('returns 404 if the postcode does not exist', function () {
    $response = $this->getJson('/api/stores/near/AB101AB');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Postcode not found.',
        ]);
});
