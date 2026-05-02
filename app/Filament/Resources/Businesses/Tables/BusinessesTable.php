<?php

namespace App\Filament\Resources\Businesses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
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
                Stack::make([
                    Split::make([
                        ImageColumn::make('logo_path')
                            ->label('')
                            ->circular()
                            ->imageSize(60)
                            ->grow(false),

                        Stack::make([
                            TextColumn::make('name')
                                ->label('Negocio')
                                ->searchable()
                                ->sortable()
                                ->weight('bold')
                                ->size(TextSize::Large),

                            TextColumn::make('category.name')
                                ->label('Categoría')
                                ->badge()
                                ->color('gray'),
                        ]),
                    ]),

                    Stack::make([
                        TextColumn::make('phone')
                            ->icon('heroicon-m-phone')
                            ->color('gray')
                            ->size(TextSize::ExtraSmall),

                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->color('gray')
                            ->size(TextSize::ExtraSmall),
                    ]),

                    Split::make([
                        TextColumn::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'Público',
                                'inactive' => 'Oculto',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'info',
                                'inactive' => 'danger',
                                default => 'gray',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'active' => 'heroicon-m-eye',
                                'inactive' => 'heroicon-m-eye-slash',
                                default => 'heroicon-m-question-mark-circle',
                            })
                            ->grow(false),

                        TextColumn::make('is_open')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Abierto' : 'Cerrado')
                            ->color(fn ($state) => $state ? 'success' : 'gray')
                            ->icon(fn ($state) => $state ? 'heroicon-m-building-storefront' : 'heroicon-m-moon')
                            ->grow(false),
                    ]),
                ])->space(3),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Filtrar por Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('city_id')
                    ->label('Ciudad')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_open')
                    ->label('Estatus de Operación')
                    ->placeholder('Todos')
                    ->trueLabel('Solo Abiertos')
                    ->falseLabel('Solo Cerrados'),

                TernaryFilter::make('status')
                    ->label('Visibilidad')
                    ->placeholder('Todos')
                    ->trueLabel('Solo Públicos')
                    ->falseLabel('Solo Ocultos')
                    ->queries(
                        true: fn ($query) => $query->where('status', 'active'),
                        false: fn ($query) => $query->where('status', 'inactive'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ]);
    }
}
