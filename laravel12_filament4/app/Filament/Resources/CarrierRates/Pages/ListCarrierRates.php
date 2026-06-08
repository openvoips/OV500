<?php

namespace App\Filament\Resources\CarrierRates\Pages;

use App\Filament\Resources\CarrierRates\CarrierRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarrierRates extends ListRecords
{
    protected static string $resource = CarrierRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
