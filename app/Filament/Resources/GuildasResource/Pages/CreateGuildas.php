<?php

namespace App\Filament\Resources\GuildasResource\Pages;

use App\Filament\Resources\GuildasResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateGuildas extends CreateRecord
{
    protected static string $resource = GuildasResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Criar Guilda');
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.guildas.index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
