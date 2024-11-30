<?php

namespace Database\Factories;

use App\Models\Classe;
use App\Models\Jogador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jogador>
 */
class JogadorFactory extends Factory
{
    protected $model = Jogador::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'classe_id' => Classe::inRandomOrder()->first()->id,
            'xp' => $this->faker->numberBetween(70, 100),
            'confirmado' => $this->faker->boolean(80), // 80% de chance de ser confirmado
        ];
    }
}
