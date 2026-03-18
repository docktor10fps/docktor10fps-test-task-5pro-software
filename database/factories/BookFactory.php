<?php

namespace Database\Factories;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(4),
            'description'    => fake()->paragraph(),
            'edition'        => fake()->numberBetween(1, 5),
            'isbn'           => fake()->isbn13(),
            'published_date' => fake()->date(),
            'pages'          => fake()->numberBetween(50, 1000),
            'format'         => fake()->randomElement(['Hardcover', 'Paperback', 'Ebook']),
            'country'        => fake()->country(),
            'publisher_id'   => Publisher::factory(),
        ];
    }
}
