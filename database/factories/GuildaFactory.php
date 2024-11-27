<?php

namespace Database\Factories;

use App\Models\Guilda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guilda>
 */
class GuildaFactory extends Factory
{
    protected $model = Guilda::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            'nome' => $this->faker->unique()->randomElement([
                'Guardiões da Luz',
                'Sombras Eternas',
                'Lâminas do Destino',
                'Sentinelas do Crepúsculo',
                'Guerreiros da Tempestade',
                'Filhos da Noite',
                'Protetores do Reino',
                'Caçadores de Dragões',
                'Irmandade da Fênix',
                'Legião dos Imortais',
                'Aliança dos Heróis',
                'Ordem dos Magos',
                'Cavaleiros da Aurora',
                'Vingadores Sombrios',
                'Clã dos Lobos',
            ]),
        ];
    }
}
