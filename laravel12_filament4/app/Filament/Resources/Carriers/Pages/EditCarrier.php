<?php

namespace App\Filament\Resources\Carriers\Pages;

use App\Filament\Resources\Carriers\CarrierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarrier extends EditRecord
{
    protected static string $resource = CarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
