<?php

namespace App\Filament\Resources\Resellers;

use App\Filament\Resources\Resellers\Pages;
use App\Filament\Resources\Resellers\Schemas\ResellerForm;
use App\Filament\Resources\Resellers\Tables\ResellersTable;
use App\Models\Reseller;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ResellerResource extends Resource
{
    protected static ?string $model = Reseller::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return ResellerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResellersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResellers::route('/'),
            'create' => Pages\CreateReseller::route('/create'),
            'edit' => Pages\EditReseller::route('/{record}/edit'),
        ];
    }
}
