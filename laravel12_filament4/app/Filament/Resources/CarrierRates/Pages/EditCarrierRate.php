<?php

namespace App\Filament\Resources\CarrierRates\Pages;

use App\Filament\Resources\CarrierRates\CarrierRateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarrierRate extends EditRecord
{
    protected static string $resource = CarrierRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
