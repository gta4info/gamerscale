<?php

namespace Database\Factories;

use App\Http\Enums\PrizeTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prize>
 */
class PrizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->name,
            'type' => fake()->randomElement(PrizeTypeEnum::cases())->value,
            'value' => fake()->numberBetween(1, 1000),
        ];
    }
}
