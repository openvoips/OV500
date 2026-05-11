<?php

namespace App\Filament\Resources\CarrierRates;

use App\Filament\Resources\CarrierRates\Pages;
use App\Filament\Resources\CarrierRates\Schemas\CarrierRateForm;
use App\Filament\Resources\CarrierRates\Tables\CarrierRatesTable;
use App\Models\CarrierRate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CarrierRateResource extends Resource
{
    protected static ?string $model = CarrierRate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Rating';

    protected static ?string $recordTitleAttribute = 'destination';

    public static function form(Schema $schema): Schema
    {
        return CarrierRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarrierRatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarrierRates::route('/'),
            'create' => Pages\CreateCarrierRate::route('/create'),
            'edit' => Pages\EditCarrierRate::route('/{record}/edit'),
        ];
    }
}
