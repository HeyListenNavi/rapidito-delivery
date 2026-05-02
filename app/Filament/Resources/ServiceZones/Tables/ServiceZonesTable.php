<?php

namespace App\Filament\Resources\ServiceZones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ServiceZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('city.name')
                    ->label('Ciudad')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Zona de Servicio')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('city.name')
                    ->label('Ciudad')
                    ->sortable()
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('active')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Operativa' : 'Inactiva')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                TextColumn::make('debug')
                    ->label('Depuración')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Apagado')
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-m-bug-ant' : 'heroicon-m-no-symbol')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery_zones_count')
                    ->counts('deliveryZones')
                    ->label('Zonas de Entrega')
                    ->badge()
                    ->color('primary')
                    ->alignEnd(),

                TextColumn::make('created_at')
                    ->label('Creada el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado de Operación')
                    ->placeholder('Todas las zonas')
                    ->trueLabel('Solo Operativas')
                    ->falseLabel('Solo Inactivas'),

                Tables\Filters\SelectFilter::make('city_id')
                    ->label('Filtrar por Ciudad')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
