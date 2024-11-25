<?php

namespace App\Filament\Resources\JogadoresResource\Pages;

use App\Filament\Resources\JogadoresResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJogadores extends CreateRecord
{
    protected static string $resource = JogadoresResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.jogadores.index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => $data['user']['password'],
            'email_verified_at' => Carbon::now(),
        ]);
        $classe = \App\Models\Classe::find($data['classe_id']);
        $img = strtolower(substr($classe->nome, 0, 1)). rand(1, 4);
        $data['image'] = "img/{$img}.jpg";
        $data['user_id'] = $user->id;
        unset($data['user']);

        return $data;
    }
}
