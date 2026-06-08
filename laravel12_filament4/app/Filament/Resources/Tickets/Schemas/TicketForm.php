<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('parent_id')->label('Parent Id')->maxLength(255),
                TextInput::make('ticket_number')->label('Ticket Number')->required()->maxLength(255),
                TextInput::make('subject')->label('Subject')->required()->maxLength(255),
                Textarea::make('content')->label('Content')->columnSpanFull(),
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                TextInput::make('company_name')->label('Company Name')->maxLength(255),
                TextInput::make('category_id')->label('Category Id')->maxLength(255),
                TextInput::make('assigned_to_id')->label('Assigned To Id')->maxLength(255),
                TextInput::make('assigned_to_user_name')->label('Assigned To User Name')->maxLength(255),
                Select::make('status')->label('Status')->options(['open' => 'open', 'closed' => 'closed', 'assigned' => 'assigned', 'working' => 'working', 'waiting-confirmation' => 'waiting-confirmation', 'not-fixed' => 'not-fixed']),
                Select::make('hide_from_customer')->label('Hide From Customer')->options(['Y' => 'Y', 'N' => 'N']),
                TextInput::make('created_by_name')->label('Created By Name')->maxLength(255),
                DateTimePicker::make('create_date')->label('Create Date'),
                DateTimePicker::make('close_date')->label('Close Date'),
            ]);
    }
}
