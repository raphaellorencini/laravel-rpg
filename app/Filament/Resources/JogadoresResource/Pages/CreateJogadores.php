<?php

namespace App\Filament\Resources\JogadoresResource\Pages;

use App\Filament\Resources\JogadoresResource;
use App\Repositories\ClasseRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateJogadores extends CreateRecord
{
    protected static string $resource = JogadoresResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Criar Jogador');
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.jogadores.index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userRepository = app(UserRepository::class);
        $classeRepository = app(ClasseRepository::class);
        $user = $userRepository->create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => $data['user']['password'],
            'email_verified_at' => Carbon::now(),
        ]);
        $classe = $classeRepository->findById($data['classe_id']);
        $img = strtolower(substr($classe->nome, 0, 1)). rand(1, 4);
        $data['image'] = "img/{$img}.jpg";
        $data['user_id'] = $user->id;
        unset($data['user']);

        return $data;
    }
}
