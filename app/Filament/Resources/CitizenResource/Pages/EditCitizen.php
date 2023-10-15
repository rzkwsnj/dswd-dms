<?php

namespace App\Filament\Resources\CitizenResource\Pages;

use App\Filament\Resources\CitizenResource;
use App\Models\Citizen;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCitizen extends EditRecord
{
    protected static string $resource = CitizenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->action(fn (Citizen $record) => $record->payouts()->count() > 0 ? Notification::make()->title('There is a citizen payout relationship, please delete the payout data first!')->warning()->send() : $record->delete()),
            Actions\RestoreAction::make(),
        ];
    }
}
