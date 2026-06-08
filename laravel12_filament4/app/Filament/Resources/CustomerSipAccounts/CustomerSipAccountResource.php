<?php

namespace App\Filament\Resources\CustomerSipAccounts;

use App\Filament\Resources\CustomerSipAccounts\Pages;
use App\Filament\Resources\CustomerSipAccounts\Schemas\CustomerSipAccountForm;
use App\Filament\Resources\CustomerSipAccounts\Tables\CustomerSipAccountsTable;
use App\Models\CustomerSipAccount;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CustomerSipAccountResource extends Resource
{
    protected static ?string $model = CustomerSipAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-phone';

    protected static string|\UnitEnum|null $navigationGroup = 'Switching';

    protected static ?string $recordTitleAttribute = 'username';

    public static function form(Schema $schema): Schema
    {
        return CustomerSipAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerSipAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerSipAccounts::route('/'),
            'create' => Pages\CreateCustomerSipAccount::route('/create'),
            'edit' => Pages\EditCustomerSipAccount::route('/{record}/edit'),
        ];
    }
}
