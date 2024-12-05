<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessoesResource\Pages;
use App\Models\Sessao;
use App\Repositories\GuildaRepository;
use App\Repositories\SessaoRepository;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class SessoesResource extends Resource
{
    protected static ?string $model = Sessao::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Sessões';

    protected static ?string $label = 'Sessões';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                TextInput::make('nome')
                    ->markAsRequired()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Get $get, Unique $rule) {
                        return $rule->where('nome', $get('nome'));
                    })
                    ->rule('required'),
                TextInput::make('qtd_guildas')
                    ->label('Qtd. Guildas')
                    ->numeric()
                    ->markAsRequired()
                    ->rules(['required', 'min:1']),
                TextInput::make('qtd_jogadores')
                    ->label('Qtd. Jogadores')
                    ->numeric()
                    ->markAsRequired()
                    ->rules(['required', 'min:1']),
            ]);
    }

    public static function table(Table $table): Table
    {
        /**
         * @var SessaoRepository $repository
         */
        $repository = app(SessaoRepository::class);

        /**
         * @var GuildaRepository $guildaRepository
         */
        $guildaRepository = app(GuildaRepository::class);

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($repository) {
                return $repository->tableList($query, Auth::id());
            })
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(query: function ($query, $search) use ($repository) {
                        return $repository->searchableFilter('nome', $query, $search);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make('viewSessoes')
                    ->hiddenLabel()
                    ->tooltip('Ver Sessões')
                    ->icon('heroicon-o-rectangle-group')
                    ->color('warning')
                    ->url(fn (Sessao $record) => static::getUrl('view-sessao', ['record' => $record->id])),
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->after(function ($record, Tables\Actions\DeleteAction $action) {
                        if ($record->guildas()->exists()) {
                            $record->guildas()->delete();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->after(function ($records, Tables\Actions\DeleteBulkAction $action) {
                        foreach ($records as $record) {
                            if ($record->guildas()->exists()) {
                                $record->guildas()->delete();
                            }
                        }
                    }),
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
            'index' => Pages\ListSessoes::route('/'),
            'create' => Pages\CreateSessoes::route('/create'),
            'edit' => Pages\EditSessoes::route('/{record}/edit'),
            'view-sessao' => Pages\ViewSessao::route('/{record}/view-sessao'),
        ];
    }
}
