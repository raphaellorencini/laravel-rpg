<?php

namespace App\Http\Controllers;

use App\Services\GuildaService;
use Illuminate\Http\Request;

class GuildasController extends Controller
{
    use AccessTrait;

    public function __construct(
        public GuildaService $guildaService,
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
        $this->guildaService->resetDatabase($guildaId);

        return $this->guildaService->balancear($jogadoresIds, $guildaId);
    }
}
