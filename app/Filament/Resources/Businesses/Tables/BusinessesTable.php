<?php

namespace App\Filament\Resources\Businesses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BusinessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('')
                    ->circular()
                    ->imageSize(40)
                    ->width(40),

                TextColumn::make('name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => $record->phone),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Público',
                        'inactive' => 'Oculto',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'active' => 'heroicon-c-eye',
                        'inactive' => 'heroicon-c-eye-slash',
                        default => null,
                    }),

                TextColumn::make('is_open')
                    ->label('Horario')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Abierto' : 'Cerrado')
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->icon(fn($state) => $state ? 'heroicon-c-building-storefront' : 'heroicon-c-moon')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('accepts_delivery')
                    ->label('Delivery')
                    ->boolean()
                    ->trueIcon('heroicon-c-shopping-bag')
                    ->falseIcon('heroicon-c-minus')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('city_id')
                    ->label('Ciudad')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_open')
                    ->label('Estado de operación')
                    ->placeholder('Todos')
                    ->trueLabel('Abiertos')
                    ->falseLabel('Cerrados'),

                SelectFilter::make('status')
                    ->label('Estado del registro')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
