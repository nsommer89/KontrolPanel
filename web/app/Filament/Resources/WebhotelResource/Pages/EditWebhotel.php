<?php

namespace App\Filament\Resources\WebhotelResource\Pages;

use App\Filament\Resources\WebhotelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebhotel extends EditRecord
{
    protected static string $resource = WebhotelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
