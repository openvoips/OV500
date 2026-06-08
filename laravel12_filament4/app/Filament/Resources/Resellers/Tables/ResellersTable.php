<?php

namespace App\Filament\Resources\Resellers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResellersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Id')->sortable()->searchable(),
                TextColumn::make('account_id')->label('Account Id')->sortable()->searchable(),
                TextColumn::make('company_name')->label('Company Name')->sortable()->searchable(),
                TextColumn::make('contact_name')->label('Contact Name')->sortable()->searchable(),
                TextColumn::make('phone')->label('Phone')->sortable()->searchable(),
                TextColumn::make('emailaddress')->label('Emailaddress')->sortable()->searchable(),
                TextColumn::make('pincode')->label('Pincode')->sortable()->searchable(),
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
