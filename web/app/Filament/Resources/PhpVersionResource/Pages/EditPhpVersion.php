<?php

namespace App\Filament\Resources\PhpVersionResource\Pages;

use App\Filament\Resources\PhpVersionResource;
use App\Models\PhpVersion;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditPhpVersion extends EditRecord
{
    protected static string $resource = PhpVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('delete')
                ->color('danger')
                ->successNotificationTitle(function () {
                    return __('PHP version was uninstalled successfully.');
                })
                ->action(function(PhpVersion $record) {
                    $record->delete();

                    return redirect(route('filament.ktrl.resources.php-versions.index', ['tenant' => Filament::getTenant()]));
                })
                ->label(function ($record) {
                    return __('Uninstall PHP :version', ['version' => $record->version]);
                })
                ->translateLabel()
                ->requiresConfirmation(),
        ];
    }
}
