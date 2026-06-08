<?php

namespace App\Filament\Resources\Dids\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('did_number')->label('Did Number')->required()->maxLength(255),
                Select::make('did_status')->label('Did Status')->options(['NEW' => 'NEW', 'USED' => 'USED', 'DEAD' => 'DEAD', 'BLOCKED' => 'BLOCKED']),
                TextInput::make('carrier_id')->label('Carrier Id')->maxLength(255),
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                DateTimePicker::make('assign_date')->label('Assign Date'),
                DateTimePicker::make('create_date')->label('Create Date'),
                TextInput::make('channels')->label('Channels')->maxLength(255),
                TextInput::make('did_name')->label('Did Name')->maxLength(255),
                Select::make('number_type')->label('Number Type')->options(['TFN' => 'TFN', 'DID' => 'DID']),
            ]);
    }
}
