<?php

namespace App\Filament\Resources\CitizenResource\Widgets;

use App\Filament\Resources\CitizenResource\Pages\ListCitizens;
use App\Models\Citizen;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CitizenStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListCitizens::class;
    }

    protected function getStats(): array
    {
        $citizenData = Trend::model(Citizen::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            Stat::make('Citizens', $this->getPageTableQuery()->count())
                ->chart(
                    $citizenData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Total Active', $this->getPageTableQuery()->whereIn('citizen_status', ['active'])->count()),
            Stat::make('Total Male', $this->getPageTableQuery()->whereIn('gender', ['male'])->count()),
            Stat::make('Total Female', $this->getPageTableQuery()->whereIn('gender', ['female'])->count()),
        ];
    }
}
