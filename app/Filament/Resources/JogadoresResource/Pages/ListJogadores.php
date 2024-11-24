<?php

namespace App\Filament\Resources\JogadoresResource\Pages;

use App\Filament\Resources\JogadoresResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJogadores extends ListRecords
{
    protected static string $resource = JogadoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
