<?php

namespace App\Filament\Resources\Dids\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DidsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('did_id')->label('Did Id')->sortable()->searchable(),
                TextColumn::make('did_number')->label('Did Number')->sortable()->searchable(),
                TextColumn::make('did_status')->label('Did Status')->sortable()->searchable(),
                TextColumn::make('carrier_id')->label('Carrier Id')->sortable()->searchable(),
                TextColumn::make('account_id')->label('Account Id')->sortable()->searchable(),
                TextColumn::make('assign_date')->label('Assign Date')->dateTime()->sortable(),
                TextColumn::make('create_date')->label('Create Date')->dateTime()->sortable(),
                TextColumn::make('channels')->label('Channels')->sortable()->searchable(),
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
