<?php

namespace App\Filament\Resources\CustomerRates\Pages;

use App\Filament\Resources\CustomerRates\CustomerRateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerRate extends EditRecord
{
    protected static string $resource = CustomerRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
