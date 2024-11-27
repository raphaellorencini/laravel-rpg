<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Guilda;
use App\Models\Jogador;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $classes = ['Guerreiro', 'Mago', 'Arqueiro', 'Clérigo'];
        foreach ($classes as $classe) {
            Classe::create(['nome' => $classe]);
        }

        $userAdmin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
        ]);
        Jogador::create([
            'user_id' => $userAdmin->id,
            'classe_id' => 1,
            'image' => 'img/g2.jpg',
            'xp' => 100,
            'confirmado' => true,
        ]);

        $userTest = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
        ]);
        Jogador::create([
            'user_id' => $userTest->id,
            'classe_id' => 2,
            'image' => 'img/a4.jpg',
            'xp' => 100,
            'confirmado' => true,
        ]);

        $titulos = [
            'da Luz', 'das Trevas', 'Arcano', 'Imortal', 'do Caos', 'Elemental', 'Supremo', 'Silencioso',
            'das Florestas', 'Fantasma', 'Veloz', 'da Cura', 'Sagrado', 'da Esperança', 'do Fogo', 'do Gelo',
            'da Tempestade', 'da Terra', 'da Morte', 'da Vida', 'do Vento', 'do Trovão', 'da Chama', 'do Abismo',
            'do Céu', 'do Inferno', 'da Noite', 'do Dia', 'do Destino', 'da Fortuna', 'da Sabedoria', 'da Força',
            'da Coragem', 'da Justiça', 'da Verdade', 'da Honra', 'da Glória', 'da Vitória', 'da Paz', 'da Guerra',
            'da Magia', 'do Mistério', 'do Segredo', 'da Alma', 'do Espírito', 'do Tempo', 'do Espaço', 'do Universo',
            'da Eternidade'
        ];
        
        $guildas = [
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
        ];
        $tituloGuildas = $guildas;
        shuffle($tituloGuildas);

        for ($i = 0; $i < 2; $i++) {
            Guilda::factory()->count(5)->sequence(function (Sequence $sequence) use ($i, &$tituloGuildas) {
                $titulo = $tituloGuildas[0];
                array_shift($tituloGuildas);
                return [
                    'nome' => $titulo,
                    'maximo_jogadores' => rand(4, 8),
                    'user_id' => $i + 1,
                ];
            })->create();
        }

        Classe::all()->each(function ($classe) use ($titulos) {
            $titulosEmbaralhados = $titulos;
            shuffle($titulosEmbaralhados);

            for ($i = 0; $i < 15; $i++) {
                $titulo = $titulosEmbaralhados[$i % count($titulosEmbaralhados)];

                $user = User::factory()->create([
                    'name' => "{$classe->nome} {$titulo}",
                    'email' => preg_replace('/[\x{0300}-\x{036F}]/ui', '', \Normalizer::normalize(mb_strtolower($classe->nome), \Normalizer::FORM_D)) . $i + 1 . '@test.com',
                ]);
                $img = strtolower(substr($classe->nome, 0, 1)). rand(1, 4);
                Jogador::create([
                    'user_id' => $user->id,
                    'classe_id' => $classe->id,
                    'image' => "img/{$img}.jpg",
                    'xp' => rand(70, 100),
                    'confirmado' => true,
                ]);
            }
        });
    }
}
