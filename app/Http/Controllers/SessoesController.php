<?php

namespace App\Http\Controllers;

use App\Repositories\SessaoRepository;
use Illuminate\Http\Request;

class SessoesController extends Controller
{
    use AccessTrait;

    public function __construct(public SessaoRepository $sessaoRepository)
    {
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
}
