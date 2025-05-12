<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating fake data for the Translation model.
 *
 * @extends Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed> The default values for the Translation model.
     */
    public function definition(): array
    {
        return [
            'key' => Str::random(10),
            'locale' => $this->faker->randomElement(['en', 'fr', 'es', 'de', 'ar']),
            'value' => $this->faker->sentence(),
            'context' => $this->faker->randomElement(['web', 'mobile', 'desktop']),
        ];
    }
}
