<?php

namespace App\Filament\Resources\CustomerSipAccounts\Pages;

use App\Filament\Resources\CustomerSipAccounts\CustomerSipAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerSipAccounts extends ListRecords
{
    protected static string $resource = CustomerSipAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
