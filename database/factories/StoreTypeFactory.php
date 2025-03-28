<?php

namespace Database\Factories;

use App\Models\StoreType;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomElement(['takeaway', 'shop', 'restaurant']),
        ];
    }
}
