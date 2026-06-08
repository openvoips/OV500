<?php

namespace App\Filament\Resources\Carriers\Pages;

use App\Filament\Resources\Carriers\CarrierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarriers extends ListRecords
{
    protected static string $resource = CarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
