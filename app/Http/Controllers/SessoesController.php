<?php

namespace App\Http\Controllers;

use App\Models\Guilda;
use App\Models\Jogador;
use App\Repositories\ClasseRepository;
use App\Repositories\SessaoRepository;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SessoesController extends Controller
{
    use AccessTrait;

    protected BalanceamentoXPStrategy $strategy;
    protected array $classes;
    protected array $guildasNomes;
    protected \Illuminate\Support\Collection $jogadores;

    public function __construct(public SessaoRepository $sessaoRepository, public ClasseRepository $classeRepository)
    {
        $this->strategy = new BalanceamentoXPStrategy();
        $this->classes = $classeRepository->getAll()->toArray();
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

    public function index(Request $request)
    {
        $data = $request->validate([
            'sessao_id' => 'required',
            'guildas' => 'required',
        ]);

        $authorized = $this->authorized($request);
        if (!$authorized) {
            return response(['error' => 'Não autorizado.'], 401);
        }

        $now = now();
        $guildas = [];
        foreach ($data['guildas'] as $guilda) {
            $guildas[$guilda] = ['created_at' => $now, 'updated_at' => $now];
        }
        $sessao = $this->sessaoRepository->findById($data['sessao_id']);
        $sessao->guildas()->detach();
        $sessao->guildas()->attach($guildas);
        //$sessao->load('guildas');
        return $sessao;
    }

    public function test(Request $request)
    {
        $authorized = $this->authorized($request);
        if (!$authorized) {
            return response(['error' => 'Não autorizado.'], 401);
        }

        $userId = 1;
        $userId2 = 2;
        $maximoJogadores = 6;
        $maximoJogadores2 = 7;

        // Criar uma sessão para o user
        $sessao = $this->sessaoRepository->create(['user_id' => $userId, 'nome' => 'Sessão de Teste '.rand(1000, 9999)]);

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
        $this->jogadoresCreate('Arqueiro', 1, 100); //xp total - 682

        $jogadores2 = $this->jogadores;
        $this->strategy->balancear($jogadores2->toArray(), $guilda2);

        return $sessao;
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
            'nome' => $this->guildasNomes[0].' '.rand(1000, 9999),
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
}
