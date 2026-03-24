<?php

namespace App\Filament\Resources\Businesses\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Apariencia del Negocio')
                            ->description('Configura cómo se verá el restaurante en la aplicación móvil.')
                            ->columns(4)
                            ->schema([
                                FileUpload::make('logo_path')
                                    ->label('Logo del negocio')
                                    ->image()
                                    ->openable()
                                    ->disk(fn() => config('filesystems.default'))
                                    ->directory('restaurants/logos')
                                    ->imageEditor()
                                    ->imageAspectRatio('1:1')
                                    ->automaticallyOpenImageEditorForAspectRatio()
                                    ->automaticallyCropImagesToAspectRatio('1:1')
                                    ->automaticallyResizeImagesToWidth('300')
                                    ->automaticallyResizeImagesToHeight('300'),

                                FileUpload::make('banner_path')
                                    ->label('Banner Promocional')
                                    ->image()
                                    ->openable()
                                    ->disk(fn() => config('filesystems.default'))
                                    ->directory('restaurants/banners')
                                    ->imageEditor()
                                    ->imageAspectRatio('3:1')
                                    ->automaticallyOpenImageEditorForAspectRatio()
                                    ->automaticallyCropImagesToAspectRatio('3:1')
                                    ->automaticallyResizeImagesToWidth('700')
                                    ->automaticallyResizeImagesToHeight('300')
                                    ->columnSpan(3),
                            ]),

                        Section::make('Clasificación y Búsqueda')
                            ->columns(2)
                            ->schema([
                                Select::make('category_id')
                                    ->label('Categoría principal')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('tags')
                                    ->label('Etiquetas (Keywords)')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Section::make('Ubicación y Logística')
                            ->description('Datos críticos para el cálculo de rutas y tarifas.')
                            ->columns(2)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Dirección exacta')
                                    ->required()
                                    ->columnSpanFull(),

                                Select::make('city_id')
                                    ->label('Ciudad')
                                    ->relationship('city', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('postal_code')
                                    ->label('Código postal')
                                    ->numeric(),

                                TextInput::make('google_maps_url')
                                    ->label('Enlace de Google Maps')
                                    ->url()
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpanFull(),

                                Fieldset::make('Coordenadas de Entrega')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('lat')
                                            ->label('Latitud')
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->required(),

                                        TextInput::make('lng')
                                            ->label('Longitud')
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->required(),
                                    ]),

                                FileUpload::make('reference_image')
                                    ->label('Foto de fachada')
                                    ->image()
                                    ->directory('restaurants/references')
                                    ->openable()
                                    ->disk(fn() => config('filesystems.default'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(2),

                Group::make()
                    ->schema([
                        Section::make('Identidad')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre comercial')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->label('URL (Slug)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ]),

                        Section::make('Estado Operativo')
                            ->schema([
                                ToggleButtons::make('status')
                                    ->label('Visibilidad')
                                    ->required()
                                    ->options(['active' => 'Público', 'inactive' => 'Oculto'])
                                    ->colors(['active' => 'success', 'inactive' => 'warning'])
                                    ->icons(['active' => 'heroicon-m-eye', 'inactive' => 'heroicon-m-eye-slash'])
                                    ->default('active')
                                    ->inline(),

                                ToggleButtons::make('is_open')
                                    ->label('¿Abierto ahora?')
                                    ->required()
                                    ->options(['true' => 'Abierto', 'false' => 'Cerrado'])
                                    ->colors(['true' => 'success', 'false' => 'gray'])
                                    ->icons(['true' => 'heroicon-m-building-storefront', 'false' => 'heroicon-m-moon'])
                                    ->formatStateUsing(fn($state) => $state ? 'true' : 'false')
                                    ->dehydrateStateUsing(fn($state) => $state === 'true')
                                    ->default('true')
                                    ->inline(),
                            ]),

                        Section::make('Canales')
                            ->schema([
                                Checkbox::make('accepts_delivery')->label('Acepta Delivery'),
                                Checkbox::make('accepts_pickup')->label('Acepta Pickup'),
                            ]),

                        Section::make('Contacto')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->prefixIcon('heroicon-m-phone'),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->prefixIcon('heroicon-m-envelope'),

                                TextInput::make('web_site')
                                    ->label('Sitio Web')
                                    ->url()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(1)
                    ->extraAttributes(['class' => 'sticky top-24']),
            ]);
    }
}
