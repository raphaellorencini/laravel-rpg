<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JogadoresResource\Pages;
use App\Models\Classe;
use App\Models\Jogador;
use App\Models\User;
use App\Repositories\JogadorRepository;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class JogadoresResource extends Resource
{
    protected static ?string $model = Jogador::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Jogadores';

    protected static ?string $label = 'Jogadores';

    public static function form(Form $form): Form
    {
        $hidden = false;
        $disable = false;
        $email = Forms\Components\TextInput::make('user.email')
            ->label('E-mail')
            ->email()
            ->markAsRequired()
            ->rule('required');
        if ($form->getLivewire() instanceof \Filament\Resources\Pages\CreateRecord) {
            $email->unique(User::class, 'users.email', ignoreRecord: true);
        }
        if ($form->getLivewire() instanceof \Filament\Resources\Pages\EditRecord) {
            $hidden = true;
            $disable = true;
            $email->disabled($disable)
                ->readOnly($disable);
        }
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do Usuário')
                    ->schema([
                        Forms\Components\Hidden::make('user.id'),
                        Forms\Components\TextInput::make('user.name')
                            ->label('Nome')
                            ->markAsRequired()
                            ->rule('required')
                            ->maxLength(255),

                        $email,

                        Forms\Components\TextInput::make('user.password')
                            ->label('Senha')
                            ->password()
                            ->markAsRequired()
                            ->rule('required')
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->visible(fn ($record) => !$record)
                            ->hidden($hidden)
                            ->confirmed(),

                        Forms\Components\TextInput::make('user.password_confirmation')
                            ->label('Confirmar Senha')
                            ->password()
                            ->requiredWith('user.password')
                            ->hidden($hidden),
                    ]),

                Forms\Components\Section::make('Dados do Jogador')
                    ->schema([
                        Forms\Components\Select::make('classe_id')
                            ->label('Classe')
                            ->markAsRequired()
                            ->rule('required')
                            ->options(Classe::pluck('nome', 'id'))
                            ->disabled($disable),
                        Forms\Components\TextInput::make('xp')
                            ->label('XP')
                            ->numeric()
                            ->markAsRequired()
                            ->rule('required')
                            ->minValue(1)
                            ->maxValue(100),

                        Forms\Components\Toggle::make('confirmado')
                            ->label('Confirmado')
                            ->default(false),
                    ]),
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
                DeleteAction::make()
                    ->after(function ($record, Tables\Actions\DeleteAction $action) {
                        if ($record->user()->exists()) {
                            $record->user()->delete();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($records, Tables\Actions\DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->user()->exists()) {
                                    $record->user()->delete();
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
            'index' => Pages\ListJogadores::route('/'),
            'create' => Pages\CreateJogadores::route('/create'),
            'edit' => Pages\EditJogadores::route('/{record}/edit'),
        ];
    }
}
