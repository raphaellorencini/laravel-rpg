<?php

namespace App\Filament\Resources\ClassesResource\Pages;

use App\Filament\Resources\ClassesResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateClasses extends CreateRecord
{
    protected static string $resource = ClassesResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Criar Classe');
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.classes.index');
    }
}
