<?php

namespace App\Filament\Resources\PhpVersionResource\Pages;

use App\Filament\Resources\PhpVersionResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePhpVersion extends CreateRecord
{
    protected static string $resource = PhpVersionResource::class;

    public static bool $canCreateAnother = false;

    public static string $createButtonLabel = 'asdsad';

    protected function getFormActions(): array
    {
        return [
            // ...parent::getFormActions(),
            Action::make('create')
            ->action('create')
            ->label('Install now')
            ->translateLabel(),
        ];
    }
}
