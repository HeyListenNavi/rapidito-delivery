<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                        TextInput::make('name')
                            ->label('Nombre de la Ciudad')
                            ->placeholder('Ej: Santo Domingo, Madrid...')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('state')
                            ->label('Estado / Provincia')
                            ->placeholder('Ej: Distrito Nacional, Comunidad de Madrid...')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->label('País')
                            ->placeholder('Ej: República Dominicana, España...')
                            ->default('República Dominicana')
                            ->required()
                            ->maxLength(255),

                        ToggleButtons::make('active')
                            ->label('Estatus Operativo')
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
                            ->default(true),
            ]);
    }
}
