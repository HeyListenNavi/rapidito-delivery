<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Filament\Resources\Businesses\BusinessResource;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la etiqueta')
                    ->placeholder('Ej: Comida Rápida, Vegano, Gourmet...')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextEntry::make('associated_businesses')
                    ->label('Negocios vinculados')
                    ->visible(fn ($record) => $record !== null)
                    ->state(function ($record) {
                        $businesses = $record->businesses()
                            ->select('businesses.id', 'name', 'status')
                            ->get()
                            ->map(fn ($business) => [
                                'name' => $business->name,
                                'status' => $business->status,
                                'url' => BusinessResource::getUrl('edit', ['record' => $business]),
                            ]);

                        return view('filament.resources.tags.associated-businesses', [
                            'businesses' => $businesses,
                        ]);
                    }),
            ]);
    }
}
