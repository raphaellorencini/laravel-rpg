<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuildasResource\Pages;
//use App\Filament\Resources\GuildasResource\RelationManagers;
use App\Models\Classe;
use App\Models\Guilda;
use App\Repositories\GuildaRepository;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class GuildasResource extends Resource
{
    protected static ?string $model = Guilda::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nome')
                    ->markAsRequired()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Get $get, Unique $rule) {
                        return $rule->where('nome', $get('nome'));
                    })
                    ->rule('required'),
                TextInput::make('maximo_jogadores')
                    ->label('Máximo de Jogadores')
                    ->numeric()
                    ->markAsRequired()
                    ->rule('required')
                    ->minValue(4)
                    ->maxValue(8),
            ]);
    }

    public static function table(Table $table): Table
    {
        $repository = app(GuildaRepository::class);

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($repository) {
                return $repository->tableList($query, Auth::user()->id);
            })
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('maximo_jogadores')
                ->label('Máx. Jogadores'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
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
            'index' => Pages\ListGuildas::route('/'),
            'create' => Pages\CreateGuildas::route('/create'),
            'edit' => Pages\EditGuildas::route('/{record}/edit'),
        ];
    }
}
