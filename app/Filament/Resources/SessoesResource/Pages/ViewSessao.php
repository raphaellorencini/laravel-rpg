<?php

namespace App\Filament\Resources\SessoesResource\Pages;

use App\Filament\Resources\SessoesResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSessao extends ViewRecord
{
    protected static string $resource = SessoesResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $schema = [];

        $schema[] = Section::make('Sessão')
            ->columns(2)
            ->schema([
                TextEntry::make('sessao')
                    ->label('Sessão')
                    ->state($this->record->nome)
            ]);

        foreach ($this->record->guildas as $guilda) {
            $schema[] = Section::make("Guilda: {$guilda->nome}")
                ->collapsible()
                ->columns(5)
                ->schema([
                    TextEntry::make('xp_total')
                        ->label('XP Total')
                        ->state($guilda->xp_total)
                        ->badge()
                        ->columnSpan(1),

                    TextEntry::make('total_jogadores')
                        ->label('Jogadores Ativos')
                        ->state($guilda->jogadores()->count())
                        ->badge()
                        ->color('warning')
                        ->columnSpan(1),

                    TextEntry::make('max_jogadores')
                        ->label('Máx. Jogadores')
                        ->state($guilda->maximo_jogadores)
                        ->badge()
                        ->color('gray')
                        ->columnSpan(1),
                    Section::make('Jogadores')->schema(function() use ($guilda){
                        $schema = [];
                        foreach ($guilda->jogadores as $jogador) {
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

                                    IconEntry::make('confirmado')
                                        ->label('Confirmado')
                                        ->boolean()
                                        ->state($jogador->confirmado)
                                        ->columnSpan(1),

                                    ImageEntry::make('image')
                                        ->state($jogador->image)
                                        ->width(80)
                                        ->height(80)
                                        ->alignCenter()
                                        ->circular()
                                        ->columnSpan(1),
                                ]);
                        }
                        return $schema;
                    })
                ]);
        }

        return $infolist->schema($schema);
    }
}
