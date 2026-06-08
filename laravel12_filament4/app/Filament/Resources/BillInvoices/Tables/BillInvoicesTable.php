<?php

namespace App\Filament\Resources\BillInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Id')->sortable()->searchable(),
                TextColumn::make('invoice_id')->label('Invoice Id')->sortable()->searchable(),
                TextColumn::make('account_id')->label('Account Id')->sortable()->searchable(),
                TextColumn::make('company_name')->label('Company Name')->sortable()->searchable(),
                TextColumn::make('email_address')->label('Email Address')->sortable()->searchable(),
                TextColumn::make('phone_number')->label('Phone Number')->sortable()->searchable(),
                TextColumn::make('bill_date')->label('Bill Date')->dateTime()->sortable(),
                TextColumn::make('billing_cycle')->label('Billing Cycle')->sortable()->searchable(),
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
