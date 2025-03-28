<?php

namespace Database\Factories;

use App\Models\Store;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'coordinates' => new Point(
                $this->faker->latitude,
                $this->faker->longitude
            ),
            'status' => $this->faker->randomElement(Status::cases()),
            'store_type_id' => $this->faker->randomElement(['takeaway', 'shop', 'restaurant']),
            'max_delivery_distance_in_meters' => $this->faker->numberBetween(500, 50000), // Between 0.5km to 50km
        ];
    }
}
