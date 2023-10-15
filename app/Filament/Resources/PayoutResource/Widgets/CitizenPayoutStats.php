<?php

namespace App\Filament\Resources\PayoutResource\Widgets;

use App\Filament\Resources\PayoutResource\Pages\ListPayouts;
use App\Models\Payout;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CitizenPayoutStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListPayouts::class;
    }

    protected function getStats(): array
    {

        return [
            Stat::make('Total Citizen Payouts', $this->getPageTableQuery()->count()),
            Stat::make(Carbon::now()->format('Y') . ' Payouts', $this->getPageTableQuery()->whereIn('payout_period_year', [Carbon::now()->format('Y')])->count()),
            Stat::make((int) Carbon::now()->format('Y') - 1 . ' Payouts', $this->getPageTableQuery()->whereIn('payout_period_year', [(int) Carbon::now()->format('Y') - 1])->count()),
            Stat::make((int) Carbon::now()->format('Y') - 2 . ' Payouts', $this->getPageTableQuery()->whereIn('payout_period_year', [(int) Carbon::now()->format('Y') - 2])->count()),
        ];
    }
}
