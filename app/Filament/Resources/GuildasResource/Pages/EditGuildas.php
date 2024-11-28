<?php

namespace App\Filament\Resources\GuildasResource\Pages;

use App\Filament\Resources\GuildasResource;
use Filament\Resources\Pages\EditRecord;

class EditGuildas extends EditRecord
{
    protected static string $resource = GuildasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
