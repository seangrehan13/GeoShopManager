<?php

use App\Models\Postcode;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns stores that can deliver to the given postcode', function () {
    $postcode = Postcode::factory()->create([
        'id' => 'AB101AB',
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.1495826, -2.1381223),
    ]);

    $nearestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149443, -2.115117),
        'max_delivery_distance_in_meters' => 1388,
    ]);

    $furthestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149521, -2.102631),
        'max_delivery_distance_in_meters' => 2141,
    ]);

    $response = $this->getJson('/api/stores/delivering-to/AB101AB');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'stores',
        ])
        ->assertJson([
            'message' => 'Stores that can deliver to the postcode retrieved successfully.',
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
                    'distance' => 2140.7519229855247,
                ],
            ],
        ]);
});

it('returns certain stores that can deliver to the given postcode', function () {
    $postcode = Postcode::factory()->create([
        'id' => 'AB101AB',
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.1495826, -2.1381223),
    ]);

    $nearestStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149443, -2.115117),
        'max_delivery_distance_in_meters' => 1388,
    ]);

    $outOfAreaStore = Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149521, -2.102631),
        'max_delivery_distance_in_meters' => 2140,
    ]);

    $response = $this->getJson('/api/stores/delivering-to/AB101AB');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'stores',
        ])
        ->assertJson([
            'message' => 'Stores that can deliver to the postcode retrieved successfully.',
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

it('returns an empty list if no stores can deliver to the postcode', function () {
    $postcode = Postcode::factory()->create([
        'id' => 'AB101AB',
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.1495826, -2.1381223),
    ]);

    // Just one meter outside of the area
    Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149443, -2.115117),
        'max_delivery_distance_in_meters' => 1387,
    ]);

    Store::factory()->create([
        'coordinates' => new \MatanYadaev\EloquentSpatial\Objects\Point(57.149521, -2.102631),
        'max_delivery_distance_in_meters' => 2140,
    ]);

    $response = $this->getJson('/api/stores/delivering-to/AB101AB');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'stores',
        ])
        ->assertJsonPath('stores', [])
        ->assertJson([
            'message' => 'Stores that can deliver to the postcode retrieved successfully.',
            'stores' => [],
        ]);
});

it('returns 404 if the postcode does not exist', function () {
    $response = $this->getJson('/api/stores/delivering-to/AB101AB');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Postcode not found.',
        ]);
});
