<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Guilda;
use App\Models\Jogador;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Arr;
use Tests\TestCase;

class BalanceamentoXPStrategyTest extends TestCase
{
    use DatabaseMigrations;

    protected BalanceamentoXPStrategy $strategy;
    protected array $classes;
    protected array $guildasNomes;
    protected int $xpMaximo;
    protected int $xpMinimo;
    protected \Illuminate\Support\Collection $jogadores;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->strategy = new BalanceamentoXPStrategy();
        $this->xpMaximo = rand(90, 100);
        $this->xpMinimo = $this->xpMaximo - (int)floor($this->xpMaximo * 0.15);
        $this->classes = Classe::all()->toArray();
        $this->guildasNomes = [
            'Punhos de Aço',
            'Mestres da Ilusão',
            'Filhos do Caos',
            'Guardiões do Éden',
            'Espectros da Névoa',
            'Asas da Liberdade',
            'Clã da Serpente',
            'Ordem da Lua Negra',
            'Sentinelas da Floresta',
            'Martelo da Justiça',
            'Legião de Ferro',
            'Mãos da Cura',
            'Olhos da Águia',
            'Espíritos da Natureza',
            'Círculo dos Elementos',
            'Guerreiros do Sol',
            'Anjos da Vingança',
            'Demônios da Guerra',
            'Sociedade Secreta',
            'Renegados do Destino',
        ];
        shuffle($this->guildasNomes);
        $this->jogadores = collect();
    }

    protected function classeId(string $nome): int
    {
        $classes = $this->classes;
        $data = Arr::first($classes, function (array $value) use ($nome) {
            return $value['nome'] === $nome;
        });
        return $data['id'];
    }

    protected function guildaCreate(int $userId, int $maximoJogadores): Collection
    {
        return Guilda::factory()->count(1)->create([
            'nome' => $this->guildasNomes[0],
            'user_id' => $userId,
            'maximo_jogadores' => $maximoJogadores,
        ]);
    }

    protected function jogadoresCreate(string $classe, $quantidadeJogadores = 1, bool $xpMaximo = false): void
    {
        $xp = $this->xpMaximo;
        for ($i = 0; $i < $quantidadeJogadores; $i++) {
            if (!$xpMaximo) {
                $xp = rand($this->xpMinimo, $this->xpMaximo-1);
            }
            $jogador = Jogador::factory()->create(['confirmado' => true, 'classe_id' => $this->classeId($classe), 'xp' => $xp]);
            $jogador->classe_nome = $jogador->classe->nome;
            $jogador->load('user');
            $this->jogadores->add($jogador);
        }
    }

    #[Test]
    public function test_balanceamento_com_sucesso()
    {
        $userId = 1;
        $maximoJogadores = 6;

        // Criar guildas e jogadores de teste
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar jogadores com classes essenciais e XP variado
        $this->jogadoresCreate('Guerreiro');

        $this->jogadoresCreate('Guerreiro', 3);
        $this->jogadoresCreate('Clérigo', 4);
        $this->jogadoresCreate('Mago', 4);
        $this->jogadoresCreate('Arqueiro', 4);
        $jogadores = $this->jogadores;

        // Chamar o método de balanceamento
        $resultado = $this->strategy->balancear($jogadores->toArray(), $guildas);

        // Verificar que não há erros
        $this->assertIsArray($resultado);
        $this->assertArrayNotHasKey('error', $resultado, 'Balanceamento falhou com erro.');

        // Verificar se cada guilda tem pelo menos 4 jogadores
        foreach ($guildas as $guilda) {
            $guilda->load('jogadores');
            $this->assertGreaterThanOrEqual(4, $guilda->jogadores()->count());
        }
    }

    #[Test]
    public function test_balanceamento_falha_com_jogadores_insuficientes()
    {
        $userId = 1;
        $maximoJogadores = 5;

        // Criar guilda usando o método auxiliar
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar jogadores usando o método auxiliar (apenas 3 jogadores)
        $this->jogadoresCreate('Guerreiro', xpMaximo: true);
        $this->jogadoresCreate('Clérigo');
        $this->jogadoresCreate('Mago');
        $jogadores = $this->jogadores;

        $resultado = $this->strategy->balancear($jogadores->toArray(), $guildas);

        // Verifica se retorna erro
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals('Número insuficiente de jogadores para formar a guilda.', $resultado['error']);
    }

    #[Test]
    public function test_balanceamento_falha_sem_classes_essenciais()
    {
        $userId = 1;
        $maximoJogadores = 4;

        // Criar guilda usando o método auxiliar
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar jogadores usando o método auxiliar (sem Guerreiro ou Clérigo)
        $this->jogadoresCreate('Guerreiro', xpMaximo: true);
        $this->jogadoresCreate('Guerreiro', 1);
        $this->jogadoresCreate('Clérigo', 2);
        $jogadores = $this->jogadores;

        $resultado = $this->strategy->balancear($jogadores->toArray(), $guildas);

        // Verifica se retorna erro
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals('Está faltando um Mago ou Arqueiro para completar a guilda.', $resultado['error']);
    }

    ###########################################
    #[Test]
    public function test_sugestao_falta_1_clerigo()
    {
        $userId = 1;
        $maximoJogadores = 8;

        // Criar guilda com o método auxiliar
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar 2 Guerreiros e 1 Clérigo usando o método auxiliar
        $this->jogadoresCreate('Guerreiro', 3);
        $this->jogadoresCreate('Clérigo', 1);

        $resultado = $this->strategy->balancear($this->jogadores->toArray(), $guildas);

        // Verifica a sugestão correta
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals(
            'Está faltando um Mago ou Arqueiro para completar a guilda. Adicione 1 Clérigo(s) para equilibrar.',
            $resultado['error']
        );
    }

    #[Test]
    public function test_sugestao_falta_1_guerreiro()
    {
        $userId = 1;
        $maximoJogadores = 8;

        // Criar guilda com o método auxiliar
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar 2 Guerreiro e 3 Clérigos
        $this->jogadoresCreate('Guerreiro', 2);
        $this->jogadoresCreate('Clérigo', 3);

        $resultado = $this->strategy->balancear($this->jogadores->toArray(), $guildas);

        // Verifica a sugestão correta
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals(
            'Está faltando um Mago ou Arqueiro para completar a guilda. Adicione 1 Guerreiro(s) para equilibrar.',
            $resultado['error']
        );
    }

    #[Test]
    public function test_sugestao_falta_2_clerigos()
    {
        $userId = 1;
        $maximoJogadores = 8;

        // Criar guilda com o método auxiliar
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar 3 Guerreiros
        $this->jogadoresCreate('Guerreiro', 4);

        $resultado = $this->strategy->balancear($this->jogadores->toArray(), $guildas);

        // Verifica a sugestão correta
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals(
            'Está faltando um Mago ou Arqueiro para completar a guilda. Adicione 2 Clérigo(s) para equilibrar.',
            $resultado['error']
        );
    }

    #[Test]
    public function test_sugestao_falta_2_guerreiros()
    {
        $userId = 1;
        $maximoJogadores = 8;

        // Criar guilda com o método auxiliar
        $guildas = $this->guildaCreate($userId, $maximoJogadores);

        // Criar 4 Clérigos
        $this->jogadoresCreate('Guerreiro', 2);
        $this->jogadoresCreate('Clérigo', 4);

        $resultado = $this->strategy->balancear($this->jogadores->toArray(), $guildas);

        // Verifica a sugestão correta
        $this->assertArrayHasKey('error', $resultado);
        $this->assertEquals(
            'Está faltando um Mago ou Arqueiro para completar a guilda. Adicione 2 Guerreiro(s) para equilibrar.',
            $resultado['error']
        );
    }
}
