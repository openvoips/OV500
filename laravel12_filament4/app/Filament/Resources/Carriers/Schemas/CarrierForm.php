<?php

namespace App\Filament\Resources\Carriers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CarrierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('carrier_id')->label('Carrier Id')->maxLength(255),
                TextInput::make('carrier_name')->label('Carrier Name')->required()->maxLength(255),
                TextInput::make('tariff_id')->label('Tariff Id')->maxLength(255),
                Select::make('carrier_type')->label('Carrier Type')->options(['INBOUND' => 'INBOUND', 'OUTBOUND' => 'OUTBOUND']),
                TextInput::make('carrier_status')->label('Carrier Status')->maxLength(255),
                TextInput::make('carrier_cps')->label('Carrier Cps')->maxLength(255),
                TextInput::make('carrier_cc')->label('Carrier Cc')->maxLength(255),
                TextInput::make('provider_id')->label('Provider Id')->maxLength(255),
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                DateTimePicker::make('created_dt')->label('Created Dt'),
                DateTimePicker::make('updated_dt')->label('Updated Dt'),
            ]);
    }
}
