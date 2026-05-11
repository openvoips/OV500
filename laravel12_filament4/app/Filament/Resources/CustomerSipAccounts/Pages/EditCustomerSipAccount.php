<?php

namespace App\Filament\Resources\CustomerSipAccounts\Pages;

use App\Filament\Resources\CustomerSipAccounts\CustomerSipAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerSipAccount extends EditRecord
{
    protected static string $resource = CustomerSipAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
