<?php

namespace App\Filament\Resources\BillInvoices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BillInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_id')->label('Invoice Id')->required()->maxLength(255),
                TextInput::make('account_id')->label('Account Id')->required()->maxLength(255),
                TextInput::make('company_name')->label('Company Name')->maxLength(255),
                Textarea::make('email_address')->label('Email Address')->columnSpanFull(),
                TextInput::make('phone_number')->label('Phone Number')->maxLength(255),
                DateTimePicker::make('bill_date')->label('Bill Date'),
                Select::make('billing_cycle')->label('Billing Cycle')->options(['weekly' => 'weekly', 'monthly' => 'monthly', 'MONTHLY' => 'MONTHLY', 'DAILY' => 'DAILY', 'WEEKLY' => 'WEEKLY']),
                TextInput::make('payment_terms')->label('Payment Terms')->maxLength(255),
                DateTimePicker::make('next_billing_date')->label('Next Billing Date'),
                DateTimePicker::make('billing_date_from')->label('Billing Date From'),
                DateTimePicker::make('billing_date_to')->label('Billing Date To'),
                TextInput::make('last_bill_amount')->label('Last Bill Amount')->maxLength(255),
                TextInput::make('currency_symbol')->label('Currency Symbol')->maxLength(255),
                TextInput::make('payments')->label('Payments')->maxLength(255),
            ]);
    }
}
