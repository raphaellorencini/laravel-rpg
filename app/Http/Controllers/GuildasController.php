<?php

namespace App\Http\Controllers;

use App\Repositories\GuildaRepository;
use App\Repositories\JogadorRepository;
use App\Strategy\BalanceamentoXPStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuildasController extends Controller
{
    public function __construct(
        public JogadorRepository $jogadorRepository,
        public GuildaRepository $guildaRepository,
        public BalanceamentoXPStrategy $balanceamentoXPStrategy,
    )
    {
    }

    public function index(Request $request)
    {
        $data = $request->all();

        $jogadoresIds = $data['jogadores'];
        $guildaId = $data['guilda'];

        $apiAccessKey = $data['api_access_key'] ?? null;
        if (isset($data['api_access_key'])) {
            $apiAccessKey = decrypt($data['api_access_key']);
        }
        if ($apiAccessKey !== config('app.api_access_key')) {
            abort(401);
        }

        //Reseta Dados da Guilda
        DB::table('guildas')->where('id', 1)->update(['xp_total' => 0]);
        DB::table('guilda_jogador')->where('guilda_id', $guildaId)->delete();

        $guildas = $this->guildaRepository->findByFields(['id' => [$guildaId]]);
        $jogadores = $this->jogadorRepository->findByFields([
            'confirmado' => true,
            'jogadores.id' => $jogadoresIds
        ]);

        return $this->balanceamentoXPStrategy->balancear($jogadores->toArray(), $guildas);
    }
}
