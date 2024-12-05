<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Arr;
use Tests\TestCase;
use App\Models\Guilda;
use App\Models\Sessao;
use App\Models\Jogador;
use App\Models\Classe;

class SessoesFeatureTest extends TestCase
{
    use DatabaseMigrations;

    protected array $classes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->classes = Classe::all()->toArray();
    }

    protected function classeId(string $nome): int
    {
        $classes = $this->classes;
        $data = Arr::first($classes, function (array $value) use ($nome) {
            return $value['nome'] === $nome;
        });
        return $data['id'];
    }

    #[Test]
    public function test_criar_sessao_com_guildas_e_jogadores()
    {
        $userId = 1;
        // Criar jogadores
        $jogadores = collect([
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Guerreiro'), 'user_id' => $userId]),
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Clérigo'), 'user_id' => $userId]),
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Mago'), 'user_id' => $userId]),
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Arqueiro'), 'user_id' => $userId]),
        ])->flatten(1);

        // Dados da requisição
        $data = [
            'user_id' => $userId,
            'qtd_guildas' => 3,
            'qtd_jogadores' => 16,
            'api_access_key' => encrypt(config('app.api_access_key')),
        ];

        // Fazer a requisição POST para criar a sessão
        $response = $this->postJson(route('api.sessoes.criar'), $data);

        // Verificar o status da resposta
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'user_id',
            'nome',
            'qtd_guildas',
            'qtd_jogadores'
        ]);

        // Verificar se a sessão foi criada no banco
        $this->assertDatabaseHas('sessoes', [
            'user_id' => $userId,
            'qtd_guildas' => 3,
            'qtd_jogadores' => 16,
        ]);

        // Verificar se as guildas foram criadas
        $this->assertCount(3, Sessao::first()->guildas);

        // Verificar se os jogadores foram vinculados às guildas
        $totalJogadoresVinculados = Guilda::with('jogadores')->get()->sum(fn($guilda) => $guilda->jogadores->count());

        $this->assertGreaterThan(4, $totalJogadoresVinculados);
    }

    #[Test]
    public function test_criar_sessao_com_guildas_e_jogadores_sem_jogadores_suficientes()
    {
        $userId = 2;
        // Criar jogadores
        $jogadores = collect([
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Guerreiro'), 'user_id' => $userId]),
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Clérigo'), 'user_id' => $userId]),
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Mago'), 'user_id' => $userId]),
            Jogador::factory()->count(10)->create(['classe_id' => $this->classeId('Arqueiro'), 'user_id' => $userId]),
        ])->flatten(1);

        // Dados da requisição
        $data = [
            'user_id' => $userId,
            'nome' => 'Teste '.rand(10000, 99999),
            'qtd_guildas' => 1,
            'qtd_jogadores' => 1,
            'api_access_key' => encrypt(config('app.api_access_key')),
        ];

        // Fazer a requisição POST para criar a sessão
        $response = $this->postJson(route('api.sessoes.criar'), $data);
        $resultado = $response->json();

        // Verificar o status da resposta
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals(
            'A guilda não possui jogadores suficientes. Ajuste o número total de jogadores para garantir pelo menos 1 Guerreiro e 1 Clérigo por guilda.',
            $resultado['error']
        );
    }
}
