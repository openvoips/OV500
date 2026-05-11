<?php

namespace App\Filament\Resources\Resellers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ResellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                TextInput::make('company_name')->label('Company Name')->required()->maxLength(255),
                TextInput::make('contact_name')->label('Contact Name')->maxLength(255),
                TextInput::make('phone')->label('Phone')->maxLength(255),
                Textarea::make('emailaddress')->label('Emailaddress')->columnSpanFull(),
                TextInput::make('pincode')->label('Pincode')->maxLength(255),
            ]);
    }
}
