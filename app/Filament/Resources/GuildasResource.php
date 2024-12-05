<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuildasResource\Pages;
use App\Models\Guilda;
use App\Repositories\ClasseRepository;
use App\Repositories\GuildaRepository;
use App\Repositories\JogadorRepository;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;


class GuildasResource extends Resource
{
    protected static ?string $model = Guilda::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Guildas';

    protected static ?string $label = 'Guildas';

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
        /**
         * @var GuildaRepository $repository
         */
        $repository = app(GuildaRepository::class);

        /**
         * @var JogadorRepository $jogadorRepository
         */
        $jogadorRepository = app(JogadorRepository::class);

        /**
         * @var ClasseRepository $classeRepository
         */
        $classeRepository = app(ClasseRepository::class);

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($repository) {
                return $repository->tableList($query);
            })
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('maximo_jogadores')
                    ->alignCenter()
                    ->label('Máx. Jogadores'),
                TextColumn::make('jogadores_count')
                    ->label('Total de Jogadores')
                    ->alignCenter()
                    ->counts('jogadores'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make('viewJogadores')
                    ->hiddenLabel()
                    ->tooltip('Ver Jogadores')
                    ->icon('heroicon-o-users')
                    ->color('warning')
                    ->url(fn (Guilda $record) => static::getUrl('view-guilda-jogadores', ['record' => $record->id])),
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
            'view-guilda-jogadores' => Pages\ViewGuildaJogadores::route('/{record}/view-guilda-jogadores'),
        ];
    }
}
