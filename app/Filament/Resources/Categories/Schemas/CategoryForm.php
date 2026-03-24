<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                ToggleButtons::make('is_active')
                    ->label('Visibilidad Global')
                    ->helperText('Si se oculta, ningún restaurante de esta categoría se mostrará')
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
            ]);
    }
}
