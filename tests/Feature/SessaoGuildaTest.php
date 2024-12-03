<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Guilda;
use App\Models\Jogador;
use App\Models\Sessao;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SessaoGuildaTest extends TestCase
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
            'nome' => 'Test '.rand(1000, 9999),
            'user_id' => $userId,
            'maximo_jogadores' => $maximoJogadores,
        ]);
    }

    protected function jogadoresCreate(string $classe, $quantidadeJogadores = 1, ?int $xp = null): void
    {
        for ($i = 0; $i < $quantidadeJogadores; $i++) {
            $jogador = Jogador::factory()->create(['confirmado' => true, 'classe_id' => $this->classeId($classe), 'xp' => $xp]);
            $jogador->classe_nome = $jogador->classe->nome;
            $jogador->load('user');
            $this->jogadores->add($jogador);
        }
    }

    #[Test]
    public function test_sessao_com_guildas_com_diferenca_de_xp()
    {
        $userId = 1;
        $userId2 = 2;
        $maximoJogadores = 6;
        $maximoJogadores2 = 7;

        // Criar uma sessão para o user
        $sessao = Sessao::factory()->create(['user_id' => $userId, 'nome' => 'Sessão de Teste']);

        // Criar guildas e jogadores de teste
        $guilda1 = $this->guildaCreate($userId, $maximoJogadores);
        $this->jogadoresCreate('Guerreiro', 2, 95);
        $this->jogadoresCreate('Clérigo', 2, 98);
        $this->jogadoresCreate('Mago', 1, 96);
        $this->jogadoresCreate('Arqueiro', 1, 100); //xp total - 582
        $jogadores = $this->jogadores;
        $this->strategy->balancear($jogadores->toArray(), $guilda1);

        $guilda2 = $this->guildaCreate($userId2, $maximoJogadores2);
        $this->jogadores = collect();//limpa os jogadores
        $this->jogadoresCreate('Guerreiro', 2, 95);
        $this->jogadoresCreate('Clérigo', 2, 98);
        $this->jogadoresCreate('Mago', 1, 97);
        $this->jogadoresCreate('Mago', 1, 100);
        $this->jogadoresCreate('Arqueiro', 1, 100); //xp total - 683

        $jogadores2 = $this->jogadores;
        $this->strategy->balancear($jogadores2->toArray(), $guilda2);

        // Adicionar guildas à sessão
        if (in_array(abs($guilda1[0]->xp_total - $guilda2[0]->xp_total), [0, 100])) {
            $sessao->guildas()->attach([$guilda1[0]->id, $guilda2[0]->id]);
        }

        // Recarrega guildas para calcular XP total
        $guilda1[0]->refresh();
        $guilda2[0]->refresh();

        // Verifica diferença de XP total entre guildas
        $this->assertTrue(
            !in_array(abs($guilda1[0]->xp_total - $guilda2[0]->xp_total), [0, 100]) && empty($sessao->guildas()->count()),
            'A diferença de XP entre as guildas não está dentro de ±100 pontos.'
        );
    }

    #[Test]
    public function test_sessao_com_guildas_com_xp_equilibrado()
    {
        $userId = 1;
        $userId2 = 2;
        $maximoJogadores = 6;
        $maximoJogadores2 = 7;

        // Criar uma sessão para o user
        $sessao = Sessao::factory()->create(['user_id' => $userId, 'nome' => 'Sessão de Teste']);

        // Criar guildas e jogadores de teste
        $guilda1 = $this->guildaCreate($userId, $maximoJogadores);
        $this->jogadoresCreate('Guerreiro', 2, 95);
        $this->jogadoresCreate('Clérigo', 2, 98);
        $this->jogadoresCreate('Mago', 1, 96);
        $this->jogadoresCreate('Arqueiro', 1, 100); //xp total - 582
        $jogadores = $this->jogadores;
        $this->strategy->balancear($jogadores->toArray(), $guilda1);

        $guilda2 = $this->guildaCreate($userId2, $maximoJogadores2);
        $this->jogadores = collect();//limpa os jogadores
        $this->jogadoresCreate('Guerreiro', 2, 95);
        $this->jogadoresCreate('Clérigo', 2, 98);
        $this->jogadoresCreate('Mago', 1, 96);
        $this->jogadoresCreate('Mago', 1, 100);
        $this->jogadoresCreate('Arqueiro', 1, 100); //xp total - 683

        $jogadores2 = $this->jogadores;
        $this->strategy->balancear($jogadores2->toArray(), $guilda2);

        // Adicionar guildas à sessão
        if (in_array(abs($guilda1[0]->xp_total - $guilda2[0]->xp_total), [0, 100])) {
            $sessao->guildas()->attach([$guilda1[0]->id, $guilda2[0]->id]);
        }

        // Recarrega guildas para calcular XP total
        $guilda1[0]->refresh();
        $guilda2[0]->refresh();

        // Verifica diferença de XP total entre guildas
        $this->assertTrue(
            in_array(abs($guilda1[0]->xp_total - $guilda2[0]->xp_total), [0, 100]) && !empty($sessao->guildas()->count()),
            'A diferença de XP entre as guildas está dentro de ±100 pontos.'
        );
    }
}
