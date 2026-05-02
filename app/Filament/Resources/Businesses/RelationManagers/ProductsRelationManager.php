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
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Detalles del Producto')
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
                Split::make([
                    ImageColumn::make('image_path')
                        ->label('')
                        ->imageSize(150)
                        ->defaultImageUrl('https://placehold.co/150x150?text=Sin+imagen')
                        ->grow(false)
                        ->extraAttributes(['style' => 'border-radius: 16px; overflow: hidden;']),

                    Stack::make([
                        TextColumn::make('name')
                            ->label('Producto')
                            ->searchable()
                            ->weight('bold')
                            ->size(TextSize::Large),

                        TextColumn::make('category.name')
                            ->label('Categoría')
                            ->badge()
                            ->color('gray'),
                    ])->space(1),

                    Stack::make([
                        TextColumn::make('price')
                            ->label('Precio')
                            ->money('MXN')
                            ->weight('bold')
                            ->color('primary')
                            ->size(TextSize::Large),

                        Stack::make([
                            TextColumn::make('is_available')
                                ->badge()
                                ->formatStateUsing(fn($state) => $state ? 'En Stock' : 'Agotado')
                                ->color(fn($state) => $state ? 'success' : 'danger')
                                ->icon(fn($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                                ->grow(false),

                            TextColumn::make('is_active')
                                ->badge()
                                ->formatStateUsing(fn($state) => $state ? 'Público' : 'Oculto')
                                ->color(fn($state) => $state ? 'info' : 'warning')
                                ->icon(fn($state) => $state ? 'heroicon-m-eye' : 'heroicon-m-eye-slash')
                                ->grow(false),
                        ])
                            ->space(1)
                            ->alignEnd(),
                    ])
                        ->space(2)
                        ->grow(false)
                        ->alignEnd(),
                ]),
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 2,
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
