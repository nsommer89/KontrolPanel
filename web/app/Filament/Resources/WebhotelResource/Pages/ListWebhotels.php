<?php

namespace App\Filament\Resources\WebhotelResource\Pages;

use App\Filament\Resources\WebhotelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebhotels extends ListRecords
{
    protected static string $resource = WebhotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
