<?php

namespace App\Filament\Resources\CitizenResource\Pages;

use App\Filament\Resources\CitizenResource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateCitizen extends CreateRecord
{
    use HasWizard;

    protected static string $resource = CitizenResource::class;

    protected function afterCreate(): void
    {
        $citizen = $this->record;

        Notification::make()
            ->title('New Citizens')
            ->icon('heroicon-o-user')
            ->body("**{$citizen->last_name}, {$citizen->first_name} {$citizen->middle_name} successfully created.**")
            ->actions([
                Action::make('View')
                    ->url(CitizenResource::getUrl('edit', ['record' => $citizen])),
            ])
            ->sendToDatabase(auth()->user());
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Citizen Data')
                ->schema([
                    Section::make()->schema(CitizenResource::getFormSchema())->columns(),
                ]),

            Step::make('Personal Informations')
                ->schema([
                    Section::make()->schema(CitizenResource::getFormSchema('personal_info'))->columns(),
                ]),

            Step::make('Address')
                ->schema([
                    Section::make()->schema(CitizenResource::getFormSchema('address'))->columns(),
                ]),

            Step::make('Utilizations of Social Pension')
                ->schema([
                    Section::make()->schema(CitizenResource::getFormSchema('utilizations')),
                ]),
        ];
    }
}
