<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuildasResource\Pages;
use App\Models\Guilda;
use App\Repositories\ClasseRepository;
use App\Repositories\GuildaRepository;
use App\Repositories\JogadorRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\Repeater;


class GuildasResource extends Resource
{
    protected static ?string $model = Guilda::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 0;

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
                return $repository->tableList($query, Auth::id());
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
                Action::make('adicionarJogadores')
                    ->hiddenLabel()
                    ->tooltip('Adicionar Jogadores')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Repeater::make('jogadores')
                            ->defaultItems(4)
                            ->columns(2)
                            ->addActionLabel('Adicionar Mais Jogadores')
                            ->schema([
                                Select::make('classe_id')
                                    ->label('Classe')
                                    ->options($classeRepository->getAll()->pluck('nome', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('jogador_id', null)),

                                Select::make('jogador_id')
                                    ->label('Jogador')
                                    ->options(function (callable $get) use ($jogadorRepository) {
                                        $classeId = $get('classe_id');
                                        if ($classeId) {
                                            return $jogadorRepository
                                                ->listByClass(['classe_id' => $classeId]);
                                        }
                                        return [];
                                    })
                                    ->disabled(fn (callable $get) => !$get('classe_id')),
                            ])
                    ])
                    ->action(function (array $data, Guilda $record) {
                        $apiAccessKey = encrypt(config('app.api_access_key'));

                        // Enviar os dados do repeater para a rota /guildas/salvar
                        $response = Http::post(route('api.guildas.salvar'), [
                            'guilda' => $record->id,
                            'jogadores' => collect($data['jogadores'])->pluck('jogador_id')->toArray(),
                            'api_access_key' => $apiAccessKey,
                        ]);
                        $responseData = $response->json();

                        // Lidar com a resposta da requisição
                        if ($response->successful() && !isset($responseData['error'])) {
                            // Exibir mensagem de sucesso
                            Notification::make()
                                ->success()
                                ->title('Jogadores adicionados com sucesso!')
                                ->send();
                        } else {
                            // Exibir mensagem de erro
                            Notification::make()
                                ->warning()
                                ->title('Erro ao adicionar jogadores.')
                                ->body($responseData['error'])
                                ->send();
                        }
                    }),
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
