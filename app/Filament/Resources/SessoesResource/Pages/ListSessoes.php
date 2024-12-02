<?php

namespace App\Filament\Resources\SessoesResource\Pages;

use App\Filament\Resources\SessoesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSessoes extends ListRecords
{
    protected static string $resource = SessoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
