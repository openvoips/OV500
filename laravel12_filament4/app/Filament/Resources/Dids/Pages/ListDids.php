<?php

namespace App\Filament\Resources\Dids\Pages;

use App\Filament\Resources\Dids\DidResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDids extends ListRecords
{
    protected static string $resource = DidResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
