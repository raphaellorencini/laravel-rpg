<?php

namespace App\Filament\Resources\GuildasResource\Pages;

use App\Filament\Resources\GuildasResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewGuildaJogadores extends ViewRecord
{
    protected static string $resource = GuildasResource::class;


    public function infolist(Infolist $infolist): Infolist
    {
        $schema = [];
        foreach ($this->record->jogadores as $jogador) {
            $schema[] = Section::make()
                ->columns(8)
                ->schema([
                    TextEntry::make('user.name')
                        ->label('Nome')
                        ->state($jogador->user->name)
                        ->columnSpan(3),

                    TextEntry::make('user.email')
                        ->label('E-mail')
                        ->state($jogador->user->email)
                        ->columnSpan(2),

                    TextEntry::make('classe.nome')
                        ->label('Classe')
                        ->state($jogador->classe->nome)
                        ->columnSpan(1),

                    IconEntry::make('')
                        ->label('')
                        ->boolean()
                        ->extraAttributes(['style' => 'display: flex; justify-content: center; align-items: center; height: 75px'])
                        ->state($jogador->classe->nome)
                        ->columnSpan(1),

                    ImageEntry::make('')
                        ->state($jogador->image)
                        ->width(80)
                        ->height(80)
                        ->alignCenter()
                        ->circular()
                        ->columnSpan(1),
                ]);
        }
        return $infolist
            ->schema($schema);
    }
}
