<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JogadoresResource\Pages;
//use App\Filament\Resources\JogadoresResource\RelationManagers;
use App\Models\Classe;
use App\Models\Jogador;
use App\Repositories\JogadorRepository;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JogadoresResource extends Resource
{
    protected static ?string $model = Jogador::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Jogadores';

    protected static ?string $label = 'Jogadores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        $repository = app(JogadorRepository::class);

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($repository) {
                return $repository->tableList($query);
            })
            ->columns([
                TextColumn::make('username')
                    ->label('Nome')
                    ->searchable(query: function ($query, $search) use ($repository) {
                        return $repository->searchableFilter('users.name', $query, $search);
                    }),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(query: function ($query, $search) use ($repository) {
                        return $repository->searchableFilter('users.email', $query, $search);
                    }),
                TextColumn::make('classe_nome')
                    ->label('Classe'),
                ImageColumn::make('image')
                    ->label('Imagem')
                    ->width(80)
                    ->height(80)
                    ->alignCenter()
                    ->circular(),

                IconColumn::make('confirmado')
                    ->label('Confirmado')
                    ->alignCenter()
                    ->boolean()
            ])
            ->filters([
                Filter::make('classe')
                    ->form([
                        Select::make('nome')
                            ->label('Classe')
                            ->placeholder('Selecione a Classe')
                            ->options(
                                Classe::pluck('nome', 'id')->toArray(),
                            )
                    ])
                    ->query(function (Builder $query, array $data) use ($repository): Builder {
                        return $repository->applyFilters($query, $data);
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJogadores::route('/'),
            'create' => Pages\CreateJogadores::route('/create'),
            'edit' => Pages\EditJogadores::route('/{record}/edit'),
        ];
    }
}
