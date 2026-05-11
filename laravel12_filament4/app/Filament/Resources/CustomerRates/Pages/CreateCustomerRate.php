<?php

namespace App\Filament\Resources\CustomerRates\Pages;

use App\Filament\Resources\CustomerRates\CustomerRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerRate extends CreateRecord
{
    protected static string $resource = CustomerRateResource::class;
}
