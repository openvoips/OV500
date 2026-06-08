<?php

namespace App\Filament\Resources\Dids;

use App\Filament\Resources\Dids\Pages;
use App\Filament\Resources\Dids\Schemas\DidForm;
use App\Filament\Resources\Dids\Tables\DidsTable;
use App\Models\Did;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DidResource extends Resource
{
    protected static ?string $model = Did::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static string|\UnitEnum|null $navigationGroup = 'DID';

    protected static ?string $recordTitleAttribute = 'did_number';

    public static function form(Schema $schema): Schema
    {
        return DidForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DidsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDids::route('/'),
            'create' => Pages\CreateDid::route('/create'),
            'edit' => Pages\EditDid::route('/{record}/edit'),
        ];
    }
}
