<?php

namespace App\Filament\Resources\ServiceZones\RelationManagers;

use App\Filament\Forms\Components\PolygonMapPicker;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryZonesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveryZones';

    protected static ?string $title = 'Zonas de Entrega';

    protected static ?string $modelLabel = 'Zona de Entrega';

    protected static ?string $pluralModelLabel = 'Zonas de Entrega';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la Zona')
                    ->placeholder('Ej: Sector A, Centro Histórico...')
                    ->required()
                    ->columnSpan(6)
                    ->maxLength(255),

                TextInput::make('delivery_price')
                    ->label('Tarifa Base de Entrega')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->columnSpan(3)
                    ->inputMode('decimal'),

                ToggleButtons::make('active')
                    ->label('Estatus de la Zona')
                    ->boolean()
                    ->options([
                        true => 'Activa',
                        false => 'Inactiva',
                    ])
                    ->colors([
                        true => 'success',
                        false => 'danger',
                    ])
                    ->icons([
                        true => 'heroicon-m-check-circle',
                        false => 'heroicon-m-x-circle',
                    ])
                    ->inline()
                    ->columnSpan(3)
                    ->default(true),

                PolygonMapPicker::make('polygon_json')
                    ->label('Mapa de la Zona')
                    ->columnSpanFull()
                    ->height(600)
                    ->zoom(13)
                    ->center([18.4861, -69.9312]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Zona')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('delivery_price')
                    ->label('Precio Base')
                    ->money('MXN')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('active')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Activa' : 'Inactiva')
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->icon(fn($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('6xl'),
            ])
            ->recordActions([
                EditAction::make('manageFares')
                    ->label('Tarifas Geográficas')
                    ->icon('heroicon-m-currency-dollar')
                    ->color('info')
                    ->modalHeading(fn($record) => 'Gestionar Tarifas entre Zonas: ' . $record->name)
                    ->modalDescription('Define cuánto cuesta enviar desde esta zona hacia otras zonas específicas.')
                    ->schema([
                        Repeater::make('outgoingFares')
                            ->label('Tarifas de Salida')
                            ->relationship('outgoingFares')
                            ->schema([
                                Select::make('to_zone_id')
                                    ->relationship('toZone', 'name')
                                    ->label('Zona de Destino')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('price')
                                    ->label('Precio ($)')
                                    ->placeholder('0.00')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(1),

                                ToggleButtons::make('active')
                                    ->label('¿Tarifa Activa?')
                                    ->boolean()
                                    ->options([
                                        true => 'Si',
                                        false => 'No',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'gray',
                                    ])
                                    ->inline()
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->itemLabel(fn(array $state): ?string => $state['price'] ? ('$' . $state['price']) : null)
                            ->addActionLabel('Añadir Regla de Precio')
                            ->defaultItems(0)
                            ->collapsed(),
                    ])
                    ->modalWidth('4xl')
                    ->slideOver(),

                EditAction::make()
                    ->modalWidth('6xl'),

                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
