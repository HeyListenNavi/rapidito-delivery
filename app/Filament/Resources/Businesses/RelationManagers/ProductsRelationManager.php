<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Detalles del Platillo')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre del producto')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Descripción para el cliente')
                                    ->helperText('Explica los ingredientes principales. Una buena descripción aumenta las ventas.')
                                    ->rows(3)
                                    ->autosize()
                                    ->columnSpanFull(),

                                TextInput::make('price')
                                    ->label('Precio')
                                    ->numeric()
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->required(),
                            ]),

                        Section::make('Fotografía')
                            ->description('Las fotos deben ser cuadradas. Se recomienda quitar el fondo del producto para mantener un diseño limpio en el menú de la app.')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->hiddenLabel()
                                    ->image()
                                    ->disk(fn() => config('filesystems.default'))
                                    ->directory('products')
                                    ->imageEditor()
                                    ->imageAspectRatio('1:1')
                                    ->automaticallyOpenImageEditorForAspectRatio()
                                    ->automaticallyCropImagesToAspectRatio('1:1')
                                    ->automaticallyResizeImagesToWidth('600')
                                    ->automaticallyResizeImagesToHeight('600'),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->schema([
                        Section::make('Clasificación')
                            ->schema([
                                Select::make('product_category_id')
                                    ->label('Categoría de Menú')
                                    ->relationship(
                                        name: 'category',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn($query, $livewire) => $query->where('business_id', $livewire->ownerRecord->id)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Section::make('Estado Operativo')
                            ->schema([
                                ToggleButtons::make('is_active')
                                    ->label('Visibilidad en Menú')
                                    ->options([
                                        'true' => 'Público',
                                        'false' => 'Oculto',
                                    ])
                                    ->colors([
                                        'true' => 'success',
                                        'false' => 'warning',
                                    ])
                                    ->icons([
                                        'true' => 'heroicon-m-eye',
                                        'false' => 'heroicon-m-eye-slash',
                                    ])
                                    ->inline()
                                    ->formatStateUsing(fn($state) => $state ? 'true' : 'false')
                                    ->dehydrateStateUsing(fn($state) => $state === 'true')
                                    ->default('true'),

                                ToggleButtons::make('is_available')
                                    ->label('Inventario (Stock)')
                                    ->options([
                                        'true' => 'En Stock',
                                        'false' => 'Agotado',
                                    ])
                                    ->colors([
                                        'true' => 'primary',
                                        'false' => 'danger',
                                    ])
                                    ->icons([
                                        'true' => 'heroicon-m-check-circle',
                                        'false' => 'heroicon-m-x-circle',
                                    ])
                                    ->inline()
                                    ->formatStateUsing(fn($state) => $state ? 'true' : 'false')
                                    ->dehydrateStateUsing(fn($state) => $state === 'true')
                                    ->default('true'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->square()
                    ->imageHeight(80),

                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => str($record->description)->limit(40)),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),

                ToggleColumn::make('is_available')
                    ->label('¿Hay stock?')
                    ->onColor('success')
                    ->offColor('gray')
                    ->onIcon('heroicon-c-check')
                    ->offIcon('heroicon-c-x-mark')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label('Público')
                    ->onColor('success')
                    ->offColor('gray')
                    ->onIcon('heroicon-c-eye')
                    ->offIcon('heroicon-c-eye-slash')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
