<?php

namespace App\Filament\Resources\CustomerSipAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerSipAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')->label('Username')->required()->maxLength(255),
                TextInput::make('secret')->label('Secret')->maxLength(255),
                Textarea::make('ipaddress')->label('Ipaddress')->columnSpanFull(),
                Select::make('status')->label('Status')->options(['open' => 'open', 'closed' => 'closed', 'assigned' => 'assigned', 'working' => 'working', 'waiting-confirmation' => 'waiting-confirmation', 'not-fixed' => 'not-fixed']),
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                TextInput::make('sip_cc')->label('Sip Cc')->maxLength(255),
                TextInput::make('sip_cps')->label('Sip Cps')->maxLength(255),
                TextInput::make('extension_no')->label('Extension No')->maxLength(255),
                TextInput::make('display_name')->label('Display Name')->maxLength(255),
                TextInput::make('caller_id')->label('Caller Id')->maxLength(255),
                TextInput::make('name')->label('Name')->maxLength(255),
                Textarea::make('email_address')->label('Email Address')->columnSpanFull(),
                TextInput::make('phone_number')->label('Phone Number')->maxLength(255),
                TextInput::make('user_type')->label('User Type')->maxLength(255),
            ]);
    }
}
