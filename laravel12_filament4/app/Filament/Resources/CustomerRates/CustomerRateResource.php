<?php

namespace App\Filament\Resources\CustomerRates;

use App\Filament\Resources\CustomerRates\Pages;
use App\Filament\Resources\CustomerRates\Schemas\CustomerRateForm;
use App\Filament\Resources\CustomerRates\Tables\CustomerRatesTable;
use App\Models\CustomerRate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CustomerRateResource extends Resource
{
    protected static ?string $model = CustomerRate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Rating';

    protected static ?string $recordTitleAttribute = 'destination';

    public static function form(Schema $schema): Schema
    {
        return CustomerRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerRatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerRates::route('/'),
            'create' => Pages\CreateCustomerRate::route('/create'),
            'edit' => Pages\EditCustomerRate::route('/{record}/edit'),
        ];
    }
}
