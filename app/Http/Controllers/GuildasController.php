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
        //return $data;

        $jogadores = $data['jogadores'];
        $guilda = $data['guilda'];

        $apiAccessKey = $data['api_access_key'] ?? null;
        if (isset($data['api_access_key'])) {
            $apiAccessKey = decrypt($data['api_access_key']);
        }
        if ($apiAccessKey !== config('app.api_access_key')) {
            abort(401);
        }

        DB::table('guildas')->where('id', 1)->update(['xp_total' => 0]);
        DB::table('guilda_jogador')->where('guilda_id', '>=', $guilda)->delete();

        $guildas = $this->guildaRepository->findByFields(['id' => [$guilda]]);
        $jogadores = $this->jogadorRepository->findByFields([
            'confirmado' => true,
            'id' => $jogadores
//                [
//                    4,9,12,//14,
//                    18,//22,21,24,
//                    //38,//44,36,43,
//                    //51,58//,60,61
//                ]
        ]);

        return $this->balanceamentoXPStrategy->balancear($jogadores->toArray(), $guildas);
    }
}
