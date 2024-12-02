<?php

namespace App\Filament\Resources\SessoesResource\Pages;

use App\Filament\Resources\SessoesResource;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewSessao extends ViewRecord
{
    protected static string $resource = SessoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('excluirGuilda')
                ->label('Remover Guildas')
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->guildas()->detach();

                    Notification::make()
                        ->success()
                        ->title('Guilda excluída da sessão com sucesso!')
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $schema = [];
        $schema[] = Section::make()
            ->columns(2)
            ->schema([
                TextEntry::make('sessao')
                    ->label('Sessão')
                    ->state($this->record->nome)
            ]);
        foreach ($this->record->guildas as $guilda) {
            $schema[] = Section::make()
                ->columns(5)
                ->schema([
                    TextEntry::make('guilda')
                        ->label('Guilda')
                        ->state($guilda->nome)
                        ->columnSpan(2),
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
                ]);
        }

        return $infolist
            ->schema($schema);
    }
}
