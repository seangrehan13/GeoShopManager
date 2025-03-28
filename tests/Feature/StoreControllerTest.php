<?php

use App\Models\StoreType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a store successfully', function () {
    StoreType::factory()->create(['id' => 'shop']);

    $payload = [
        'name' => 'Test Store',
        'latitude' => 57.1495826,
        'longitude' => -2.1381223,
        'status' => 'open',
        'type' => 'shop',
        'max_delivery_distance_in_meters' => 1000,
    ];

    $response = $this->postJson('/api/store/create', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data',
        ])
        ->assertJson([
            'message' => 'Store added successfully.',
        ]);

    $this->assertDatabaseHas('stores', [
        'name' => 'Test Store',
        'status' => 'open',
        'store_type_id' => 'shop',
        'max_delivery_distance_in_meters' => 1000,
    ]);
});

it('returns validation errors when required fields are missing', function () {
    $payload = [
        'latitude' => 57.1495826,
        'longitude' => -2.1381223,
    ]; // Missing 'name', 'status', 'type', 'max_delivery_distance_in_meters'

    $response = $this->postJson('/api/store/create', $payload);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors',
        ]);
});
