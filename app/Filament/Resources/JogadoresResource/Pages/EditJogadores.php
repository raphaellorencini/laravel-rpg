<?php

namespace App\Filament\Resources\JogadoresResource\Pages;

use App\Filament\Resources\JogadoresResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJogadores extends EditRecord
{
    protected static string $resource = JogadoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
