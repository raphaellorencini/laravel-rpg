<?php

namespace App\Filament\Resources\SessoesResource\Pages;

use App\Filament\Resources\SessoesResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSessoes extends CreateRecord
{
    protected static string $resource = SessoesResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.sessoes.index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
}
