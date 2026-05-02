<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'productCategories';

    protected static ?string $title = 'Categorías del Menú';

    protected static ?string $modelLabel = 'Categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la categoría')
                    ->placeholder('Ej. Entradas, Platos Fuertes, Bebidas...')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Categoría')
                    ->searchable(),

                TextColumn::make('products_count')
                    ->label('Cantidad de productos')
                    ->counts('products')
                    ->formatStateUsing(fn($state) => "{$state} productos")
                    ->color('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('md'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('md'),

                DeleteAction::make()
                    ->before(function ($record, DeleteAction $action) {
                        if ($record->products()->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Acción denegada')
                                ->body('No puedes eliminar esta categoría porque tiene platillos asignados. Mueve o elimina los platillos primero.')
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            foreach ($records as $record) {
                                if ($record->products()->count() > 0) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Error en la selección')
                                        ->body("La categoría '{$record->name}' tiene platillos y no puede ser eliminada.")
                                        ->send();

                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ]);
    }
}
