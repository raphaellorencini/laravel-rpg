<?php

namespace App\Http\Controllers;

use App\Repositories\GuildaRepository;
use App\Repositories\JogadorRepository;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuildasController extends Controller
{
    use AccessTrait;

    public function __construct(
        public JogadorRepository $jogadorRepository,
        public GuildaRepository $guildaRepository,
        public BalanceamentoXPStrategy $balanceamentoXPStrategy,
    )
    {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'jogadores' => 'required',
            'guilda' => 'required',
        ]);

        $authorized = $this->authorized($request);
        if (!$authorized) {
            return response(['error' => 'Não autorizado.'], 401);
        }

        $jogadoresIds = $data['jogadores'];
        $guildaId = $data['guilda'];

        //Reseta Dados da Guilda
        DB::table('guildas')->where('id', $guildaId)->update(['xp_total' => 0]);
        DB::table('guilda_jogador')->where('guilda_id', $guildaId)->delete();

        $guildas = $this->guildaRepository->findByFields(['id' => [$guildaId]]);
        $jogadores = $this->jogadorRepository->findByFields([
            'confirmado' => true,
            'jogadores.id' => $jogadoresIds
        ]);

        return $this->balanceamentoXPStrategy->balancear($jogadores->toArray(), $guildas);
    }
}
