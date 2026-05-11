<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_id')->label('Ticket Id')->sortable()->searchable(),
                TextColumn::make('parent_id')->label('Parent Id')->sortable()->searchable(),
                TextColumn::make('ticket_number')->label('Ticket Number')->sortable()->searchable(),
                TextColumn::make('subject')->label('Subject')->sortable()->searchable(),
                TextColumn::make('content')->label('Content')->sortable()->searchable(),
                TextColumn::make('account_id')->label('Account Id')->sortable()->searchable(),
                TextColumn::make('company_name')->label('Company Name')->sortable()->searchable(),
                TextColumn::make('category_id')->label('Category Id')->sortable()->searchable(),
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
