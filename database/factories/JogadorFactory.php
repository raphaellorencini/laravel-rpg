<?php

namespace Database\Factories;

use App\Models\Classe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jogador>
 */
class JogadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'classe_id' => Classe::inRandomOrder()->first()->id,
            'xp' => $this->faker->numberBetween(70, 100),
            'confirmado' => $this->faker->boolean(80), // 80% de chance de ser confirmado
        ];
    }
}
