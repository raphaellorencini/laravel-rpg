<?php

namespace App\Filament\Resources\GuildasResource\Pages;

use App\Filament\Resources\GuildasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuildas extends ListRecords
{
    protected static string $resource = GuildasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
