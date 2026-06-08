<?php

namespace App\Filament\Resources\CustomerRates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ratecard_id')->label('Ratecard Id')->required()->maxLength(255),
                TextInput::make('prefix')->label('Prefix')->required()->maxLength(255),
                TextInput::make('destination')->label('Destination')->required()->maxLength(255),
                TextInput::make('setup_charge')->label('Setup Charge')->maxLength(255),
                TextInput::make('rental')->label('Rental')->maxLength(255),
                TextInput::make('rate')->label('Rate')->maxLength(255),
                TextInput::make('connection_charge')->label('Connection Charge')->maxLength(255),
                TextInput::make('minimal_time')->label('Minimal Time')->maxLength(255),
                TextInput::make('resolution_time')->label('Resolution Time')->maxLength(255),
                Select::make('rates_status')->label('Rates Status')->options(['0' => '0', '1' => '1']),
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
            ]);
    }
}
