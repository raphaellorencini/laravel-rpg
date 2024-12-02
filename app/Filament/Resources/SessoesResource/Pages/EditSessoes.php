<?php

namespace App\Filament\Resources\SessoesResource\Pages;

use App\Filament\Resources\SessoesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSessoes extends EditRecord
{
    protected static string $resource = SessoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
