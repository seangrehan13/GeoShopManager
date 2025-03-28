<?php

namespace Database\Factories;

use App\Models\Postcode;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\Point;

class PostcodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Postcode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->regexify('[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}'),
            'coordinates' => new Point(
                $this->faker->latitude,
                $this->faker->longitude
            ),
        ];
    }
}
