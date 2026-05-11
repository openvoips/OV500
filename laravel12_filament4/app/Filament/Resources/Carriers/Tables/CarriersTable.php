<?php

namespace App\Filament\Resources\Carriers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarriersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Id')->sortable()->searchable(),
                TextColumn::make('carrier_id')->label('Carrier Id')->sortable()->searchable(),
                TextColumn::make('carrier_name')->label('Carrier Name')->sortable()->searchable(),
                TextColumn::make('tariff_id')->label('Tariff Id')->sortable()->searchable(),
                TextColumn::make('carrier_type')->label('Carrier Type')->sortable()->searchable(),
                TextColumn::make('carrier_status')->label('Carrier Status')->sortable()->searchable(),
                TextColumn::make('carrier_cps')->label('Carrier Cps')->sortable()->searchable(),
                TextColumn::make('carrier_cc')->label('Carrier Cc')->sortable()->searchable(),
            ])
            ->filters([
                // Add module-specific filters during the next migration pass.
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
