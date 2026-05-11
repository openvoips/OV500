<?php

namespace App\Filament\Resources\BillInvoices\Pages;

use App\Filament\Resources\BillInvoices\BillInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBillInvoice extends EditRecord
{
    protected static string $resource = BillInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
