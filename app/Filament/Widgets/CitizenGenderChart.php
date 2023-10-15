<?php

namespace App\Filament\Widgets;

use App\Models\Citizen;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CitizenGenderChart extends ChartWidget
{
    protected static ?string $heading = 'Citizens Gender';

    protected static ?int $sort = 2;


    protected function getData(): array
    {
        $citizensGender = Citizen::select('gender', DB::raw('count(*) AS count'))
                            ->groupBy('gender')
                            ->pluck('count', 'gender')
                            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Citizens Gender ',
                    'data' => array_values($citizensGender),
                ],
            ],
            'labels' => ['MALE', 'FEMALE'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
