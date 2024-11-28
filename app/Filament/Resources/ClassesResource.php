<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassesResource\Pages;
use App\Models\Classe;
use App\Repositories\ClasseRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\Unique;

class ClassesResource extends Resource
{
    protected static ?string $model = Classe::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Classes';

    protected static ?string $label = 'Classes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nome')
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Get $get, Unique $rule) {
                        return $rule->where('nome', $get('nome'));
                    })
                    ->markAsRequired()
                    ->rule('required'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $repository = app(ClasseRepository::class);

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($repository) {
                return $repository->tableList($query);
            })
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
            ])
            ->filters([
                Filter::make('classe')
                    ->form([
                        Select::make('nome')
                            ->label('Filtro Nome')
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
                    ->before(function ($record, Tables\Actions\DeleteAction $action) {
                        if ($record->jogadores()->exists()) {
                            Notification::make()
                                ->title('Erro ao deletar')
                                ->body('Esta classe possui jogadores vinculados e não pode ser deletada.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records, Tables\Actions\DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->jogadores()->exists()) {
                                    Notification::make()
                                        ->title('Erro ao deletar')
                                        ->body("A classe {$record->nome} possui jogadores vinculados e não pode ser deletada.")
                                        ->danger()
                                        ->send();

                                    $action->cancel();
                                    break;
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
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClasses::route('/create'),
            'edit' => Pages\EditClasses::route('/{record}/edit'),
        ];
    }
}
