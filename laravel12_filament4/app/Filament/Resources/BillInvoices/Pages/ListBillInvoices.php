<?php

namespace App\Filament\Resources\BillInvoices\Pages;

use App\Filament\Resources\BillInvoices\BillInvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBillInvoices extends ListRecords
{
    protected static string $resource = BillInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
