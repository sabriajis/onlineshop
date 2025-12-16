<?php

namespace App\Filament\Admin\Resources\Shipments\Pages;

use App\Filament\Admin\Resources\Shipments\ShipmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
