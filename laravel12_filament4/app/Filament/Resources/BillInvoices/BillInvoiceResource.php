<?php

namespace App\Filament\Resources\BillInvoices;

use App\Filament\Resources\BillInvoices\Pages;
use App\Filament\Resources\BillInvoices\Schemas\BillInvoiceForm;
use App\Filament\Resources\BillInvoices\Tables\BillInvoicesTable;
use App\Models\BillInvoice;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BillInvoiceResource extends Resource
{
    protected static ?string $model = BillInvoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Billing';

    protected static ?string $recordTitleAttribute = 'invoice_id';

    public static function form(Schema $schema): Schema
    {
        return BillInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillInvoicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillInvoices::route('/'),
            'create' => Pages\CreateBillInvoice::route('/create'),
            'edit' => Pages\EditBillInvoice::route('/{record}/edit'),
        ];
    }
}
