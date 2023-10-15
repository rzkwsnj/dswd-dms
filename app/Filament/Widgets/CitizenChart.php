<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Citizen;
use Filament\Widgets\ChartWidget;

class CitizenChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Citizens Chart';

    protected static ?int $sort = 1;

    protected function getData(): array
    {

        $citizens = $this->getCitizensPerMonth();
        return [
            'datasets' => [
                [
                    'label' => 'Citizens',
                    'data' => $citizens['citizensPerMonth'],
                    'fill' => 'start',
                ],
            ],
                'labels' => $citizens['months'],
            ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getCitizensPerMonth(): array
    {
        $now = Carbon::now();

        $citizensPerMonth = [];

        // the reference sign & is added to the parameter in the function declaration, which means that the array parameter is passed by reference and will therefore be modified is intended.
        $months = collect(range(1, 12))
            ->map(function($month) use ($now, &$citizensPerMonth)
            {
                $count = Citizen::whereMonth('created_at', Carbon::parse($now->month($month)
                    ->format('Y-m')))
                    ->count();

                $citizensPerMonth[] = $count;

                return $now->month($month)
                    ->format('M');

            })->toArray();

        return [
            'citizensPerMonth' => $citizensPerMonth,
            'months' => $months
        ];
    }
}
