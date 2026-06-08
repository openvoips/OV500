<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                TextInput::make('company_name')->label('Company Name')->required()->maxLength(255),
                TextInput::make('contact_name')->label('Contact Name')->maxLength(255),
                TextInput::make('name')->label('Name')->maxLength(255),
                TextInput::make('phone')->label('Phone')->maxLength(255),
                Textarea::make('emailaddress')->label('Emailaddress')->columnSpanFull(),
                Select::make('billing_type')->label('Billing Type')->options(['prepaid' => 'prepaid', 'postpaid' => 'postpaid', 'netoff' => 'netoff']),
                Select::make('billing_cycle')->label('Billing Cycle')->options(['weekly' => 'weekly', 'monthly' => 'monthly', 'MONTHLY' => 'MONTHLY', 'DAILY' => 'DAILY', 'WEEKLY' => 'WEEKLY']),
                TextInput::make('payment_terms')->label('Payment Terms')->maxLength(255),
                DateTimePicker::make('next_billing_date')->label('Next Billing Date'),
                DateTimePicker::make('created_dt')->label('Created Dt'),
                DateTimePicker::make('updated_dt')->label('Updated Dt'),
            ]);
    }
}
