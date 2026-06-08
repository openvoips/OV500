<?php

namespace App\Filament\Resources\Carriers;

use App\Filament\Resources\Carriers\Pages;
use App\Filament\Resources\Carriers\Schemas\CarrierForm;
use App\Filament\Resources\Carriers\Tables\CarriersTable;
use App\Models\Carrier;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CarrierResource extends Resource
{
    protected static ?string $model = Carrier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static string|\UnitEnum|null $navigationGroup = 'Routing';

    protected static ?string $recordTitleAttribute = 'carrier_name';

    public static function form(Schema $schema): Schema
    {
        return CarrierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarriersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarriers::route('/'),
            'create' => Pages\CreateCarrier::route('/create'),
            'edit' => Pages\EditCarrier::route('/{record}/edit'),
        ];
    }
}
