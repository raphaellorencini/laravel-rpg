<?php

namespace Database\Factories;

use App\Models\Sessao;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sessao>
 */
class SessaoFactory extends Factory
{
    protected $model = Sessao::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            'user_id' => User::factory()->create()->id,
            'nome' => $this->faker->unique()->randomElement([
                'Sessão - Guardiões da Luz',
                'Sessão - Sombras Eternas',
                'Sessão - Lâminas do Destino',
                'Sessão - Sentinelas do Crepúsculo',
                'Sessão - Guerreiros da Tempestade',
                'Sessão - Filhos da Noite',
                'Sessão - Protetores do Reino',
                'Sessão - Caçadores de Dragões',
                'Sessão - Irmandade da Fênix',
                'Sessão - Legião dos Imortais',
                'Sessão - Aliança dos Heróis',
                'Sessão - Ordem dos Magos',
                'Sessão - Cavaleiros da Aurora',
                'Sessão - Vingadores Sombrios',
                'Sessão - Clã dos Lobos',
            ]),
        ];
    }
}
