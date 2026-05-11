<?php

namespace App\Filament\Resources\CustomerRates\Pages;

use App\Filament\Resources\CustomerRates\CustomerRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerRates extends ListRecords
{
    protected static string $resource = CustomerRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
