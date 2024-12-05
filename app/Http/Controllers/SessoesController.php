<?php

namespace App\Http\Controllers;

use App\Repositories\ClasseRepository;
use App\Repositories\GuildaRepository;
use App\Repositories\JogadorRepository;
use App\Repositories\SessaoRepository;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessoesController extends Controller
{
    use AccessTrait;

    protected BalanceamentoXPStrategy $strategy;
    protected array $classes;
    protected array $guildasNomes;
    protected \Illuminate\Support\Collection $jogadores;

    public function __construct(
        public SessaoRepository $sessaoRepository,
        public GuildaRepository $guildaRepository,
        public JogadorRepository $jogadorRepository,
        public ClasseRepository $classeRepository,
    )
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

        return $sessao;
    }

    public function criarSessao(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required',
            'qtd_guildas' => 'required',
            'qtd_jogadores' => 'required',
        ]);
        if ($request->has('nome')) {
            $data['nome'] = $request->get('nome');
        }

        /*************************************
         * RESET
         ************************************/
        /*$this->sessaoRepository->getQueryBuilder()->where('id', '>=', 1)->delete();
        DB::table('sessao_guilda')->where('sessao_id', '>=', 1)->delete();
        DB::table('guilda_jogador')->where('guilda_id', '>=', 1)->delete();*/
        /*************************************/
        DB::beginTransaction();
        $sessao = $this->sessaoRepository->create([
            'user_id' => $data['user_id'],
            'nome' => $data['nome'] ?? 'Sessão - ' . rand(10000, 99999),
            'qtd_guildas' => $data['qtd_guildas'],
            'qtd_jogadores' => $data['qtd_jogadores'],
        ]);

        // Distribuição das quantidades por classe
        $quantidadesPorClasse = $this->calcularDistribuicaoPorClasse($data['qtd_jogadores'], $data['qtd_guildas']);

        // Buscar jogadores proporcionalmente por classe
        $jogadores = $this->jogadorRepository->getJogadoresAleatorios($quantidadesPorClasse);

        $distribuicao = $this->distribuirJogadores($data['qtd_jogadores'], $data['qtd_guildas']);

        $guildasData = [];
        for ($i = 0; $i < $data['qtd_guildas']; $i++) {
            $guildasData[] = [
                'nome' => 'Guilda ' . rand(10000, 99999),
                'maximo_jogadores' => $distribuicao[$i],
                'xp_total' => 0,
            ];
        }

        $guildas = collect();
        foreach ($guildasData as $guildaData) {
            $guilda = $this->guildaRepository->getQueryBuilder()->create($guildaData);
            $guilda->load('jogadores');
            $guildas->add($guilda);
        }

        $classesNecessarias = ['Guerreiro', 'Clérigo', 'Mago', 'Arqueiro'];
        $jogadoresDistribuidos = collect();

        foreach ($guildas as $guilda) {
            foreach ($classesNecessarias as $classe) {
                $necessarios = ($guilda->maximo_jogadores > 5) ? 2 : 1; // Ajuste para guildas maiores
                $jogadoresClasse = $jogadores->where('classe_nome', $classe)->take($necessarios);

                $jogadoresDistribuidos = $jogadoresDistribuidos->merge($jogadoresClasse);
                $jogadores = $jogadores->diff($jogadoresClasse); // Remove os selecionados
            }
        }

        if ($guildas->count()) {
            $guildasIds = $guildas->pluck('id')->toArray();
            $sessao->guildas()->attach($guildasIds);
            if ($jogadores->count()) {
                $guildas
                    ->map(function ($value) {
                        $value->jogadores_count = $value->jogadores()->count();
                        return $value;
                    });
            }
        }

        $balancear = $this->strategy->balancear($jogadoresDistribuidos, $guildas);
        if (isset($balancear['error'])) {
            DB::rollBack();
            return $balancear;
        }
        DB::commit();

        return $sessao;
    }

    protected function distribuirJogadores(int $totalJogadores, int $totalGuildas): array
    {
        $jogadoresPorGuilda = array_fill(0, $totalGuildas, intdiv($totalJogadores, $totalGuildas));
        for ($i = 0; $i < ($totalJogadores % $totalGuildas); $i++) {
            $jogadoresPorGuilda[$i]++;
        }
        return $jogadoresPorGuilda; // Exemplo: [5, 5, 6] para 16 jogadores e 3 guildas
    }

    protected function calcularDistribuicaoPorClasse(int $totalJogadores, int $totalGuildas): array
    {
        // Inicialização das quantidades por classe
        $quantidades = [
            'guerreiros' => 0,
            'clerigos' => 0,
            'magos' => 0,
            'arqueiros' => 0,
        ];

        // Definindo o mínimo essencial para cada guilda
        $minimoPorGuilda = [
            'guerreiros' => 1,
            'clerigos' => 1,
            'magosOuArqueiros' => 1,
        ];

        // Calculando o total mínimo necessário de cada classe essencial
        $quantidades['guerreiros'] = $totalGuildas * $minimoPorGuilda['guerreiros'];
        $quantidades['clerigos'] = $totalGuildas * $minimoPorGuilda['clerigos'];
        $quantidades['magos'] = intdiv($totalGuildas, 2);  // Garantindo pelo menos metade das guildas com Magos
        $quantidades['arqueiros'] = $totalGuildas - $quantidades['magos'];  // O resto será de Arqueiros

        // Calcular jogadores restantes após a distribuição mínima
        $jogadoresRestantes = $totalJogadores - array_sum($quantidades);

        // Distribuir os jogadores restantes entre todas as classes proporcionalmente
        while ($jogadoresRestantes > 0) {
            foreach (['guerreiros', 'clerigos', 'magos', 'arqueiros'] as $classe) {
                if ($jogadoresRestantes > 0) {
                    $quantidades[$classe]++;
                    $jogadoresRestantes--;
                }
            }
        }

        return $quantidades;
    }

}
