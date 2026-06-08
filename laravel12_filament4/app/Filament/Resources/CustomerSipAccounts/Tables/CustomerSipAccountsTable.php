<?php

namespace App\Filament\Resources\CustomerSipAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerSipAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Id')->sortable()->searchable(),
                TextColumn::make('username')->label('Username')->sortable()->searchable(),
                TextColumn::make('secret')->label('Secret')->sortable()->searchable(),
                TextColumn::make('ipaddress')->label('Ipaddress')->sortable()->searchable(),
                TextColumn::make('status')->label('Status')->sortable()->searchable(),
                TextColumn::make('account_id')->label('Account Id')->sortable()->searchable(),
                TextColumn::make('sip_cc')->label('Sip Cc')->sortable()->searchable(),
                TextColumn::make('sip_cps')->label('Sip Cps')->sortable()->searchable(),
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
