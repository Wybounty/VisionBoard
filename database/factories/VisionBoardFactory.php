<?php

namespace Database\Factories;

use App\Models\VisionBoard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisionBoard>
 */
class VisionBoardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'year' => $this->faker->numberBetween(2020, 2030),
        ];
    }
}
