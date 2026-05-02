<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Tables;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('country')
                    ->label('País')
                    ->collapsible(),
                Group::make('state')
                    ->label('Estado / Provincia')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('state')
                    ->label('Estado')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('country')
                    ->label('País')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('active')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Activa' : 'Inactiva')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                TextColumn::make('service_zones_count')
                    ->counts('serviceZones')
                    ->label('Zonas')
                    ->badge()
                    ->color('info')
                    ->alignEnd(),

                TextColumn::make('created_at')
                    ->label('Registrada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Solo ciudades activas')
                    ->placeholder('Todas las ciudades'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
