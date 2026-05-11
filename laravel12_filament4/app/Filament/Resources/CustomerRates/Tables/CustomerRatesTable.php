<?php

namespace App\Filament\Resources\CustomerRates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rate_id')->label('Rate Id')->sortable()->searchable(),
                TextColumn::make('ratecard_id')->label('Ratecard Id')->sortable()->searchable(),
                TextColumn::make('prefix')->label('Prefix')->sortable()->searchable(),
                TextColumn::make('destination')->label('Destination')->sortable()->searchable(),
                TextColumn::make('setup_charge')->label('Setup Charge')->sortable()->searchable(),
                TextColumn::make('rental')->label('Rental')->sortable()->searchable(),
                TextColumn::make('rate')->label('Rate')->sortable()->searchable(),
                TextColumn::make('connection_charge')->label('Connection Charge')->sortable()->searchable(),
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
