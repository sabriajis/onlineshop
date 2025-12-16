<?php

namespace App\Filament\Admin\Resources\Shipments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                TextInput::make('courier')
                    ->required(),
                TextInput::make('tracking_number'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
            ]);
    }
}
