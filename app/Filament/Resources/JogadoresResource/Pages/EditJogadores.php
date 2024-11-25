<?php

namespace App\Filament\Resources\JogadoresResource\Pages;

use App\Filament\Resources\JogadoresResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJogadores extends EditRecord
{
    protected static string $resource = JogadoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = User::find($data['user_id']);
        $data['user']['id'] = $user->id ?? '';
        $data['user']['name'] = $user->name ?? '';
        $data['user']['email'] = $user->email ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = User::find($data['user']['id']);
        $user->fill([
            'name' => $data['user']['name']
        ])
        ->save();

        unset($data['image']);
        unset($data['user']);

        return $data;
    }
}
