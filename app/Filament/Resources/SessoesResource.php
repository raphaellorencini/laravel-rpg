<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessoesResource\Pages;
use App\Models\Guilda;
use App\Models\Sessao;
use App\Repositories\GuildaRepository;
use App\Repositories\SessaoRepository;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Unique;

class SessoesResource extends Resource
{
    protected static ?string $model = Sessao::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Sessões';

    protected static ?string $label = 'Sessões';

    protected static ?int $navigationSort = 1;

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
                Tables\Actions\Action::make('selecionarGuildas')
                    ->hiddenLabel()
                    ->tooltip('Selecionar Guildas')
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->form([
                        Forms\Components\Repeater::make('guildas')
                            ->defaultItems(1)
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->schema([
                                Forms\Components\Select::make('guilda1')
                                    ->label('Sua Guilda')
                                    ->options(
                                        $guildaRepository->selectField(['user_id' => Auth::id()])
                                    )
                                    ->required()
                                    ->reactive(),

                                Forms\Components\Select::make('guilda2')
                                    ->label('Guilda Adversária')
                                    ->options(function (callable $get) use ($guildaRepository) {
                                        $guilda1Id = $get('guilda1');
                                        if (!$guilda1Id) {
                                            return [];
                                        }

                                        $xpSelecionado = Guilda::find($guilda1Id)?->xp_total ?? 0;

                                        return $guildaRepository
                                            ->selectField([
                                                'not_user_id' => Auth::id(),
                                                'between_xp_total' => [$xpSelecionado - 100, $xpSelecionado + 100]
                                            ]);
                                    })
                                    ->required()
                                    ->disabled(fn (callable $get) => !$get('guilda1')),
                            ]),
                    ])
                    ->action(function (array $data, Sessao $record) {
                        $apiAccessKey = encrypt(config('app.api_access_key'));

                        $response = Http::post(route('api.sessoes.salvar'), [
                            'sessao_id' => $record->id,
                            'guildas' => [
                                $data['guildas'][0]['guilda1'],
                                $data['guildas'][0]['guilda2']
                            ],
                            'api_access_key' => $apiAccessKey,
                        ]);

                        if ($response->successful()) {
                            Notification::make()
                                ->success()
                                ->title('Guildas adicionadas com sucesso!')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Erro ao adicionar guildas.')
                                ->body('Verifique os dados e tente novamente.')
                                ->send();
                        }
                    }),
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
