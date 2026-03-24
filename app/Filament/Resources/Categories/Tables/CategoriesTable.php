<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('businesses_count')
                    ->counts('businesses')
                    ->label('Restaurantes asignados')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Público')
                    ->onColor('success')
                    ->offColor('gray')
                    ->onIcon('heroicon-c-eye')
                    ->offIcon('heroicon-c-eye-slash'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('lg'),
                DeleteAction::make()
                    ->before(function ($record, DeleteAction $action) {
                        if ($record->businesses()->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Acción denegada')
                                ->body('No puedes eliminar una categoría que tiene restaurantes asignados.')
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
                                if ($record->businesses()->count() > 0) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Error de integridad')
                                        ->body("La categoría '{$record->name}' está en uso y no puede ser eliminada.")
                                        ->send();
                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ]);
    }
}
