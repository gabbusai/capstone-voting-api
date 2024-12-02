<?php

// database/factories/StudentFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = \App\Models\Student::class;

    public function definition()
    {
        return [
            'id' => $this->faker->unique()->randomNumber(3),
            'year' => $this->faker->numberBetween(1, 4),
            'name' => $this->faker->name,
        ];
    }
}

