<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('user_id')
                    ->label('Cliente')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('business_id')
                    ->label('Restaurante')
                    ->relationship('business', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('driver_id')
                    ->label('Repartidor')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn ($case) => [
                                $case->value => str($case->value)->headline(),
                            ])
                            ->toArray()
                    )
                    ->required(),

                Forms\Components\Select::make('payment_status')
                    ->label('Estado de Pago')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'failed' => 'Fallido',
                        'refunded' => 'Reembolsado',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                Forms\Components\TextInput::make('delivery_fee')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),

                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
            ]);
    }
}
