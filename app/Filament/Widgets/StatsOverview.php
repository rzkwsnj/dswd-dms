<?php

namespace App\Filament\Widgets;

use App\Models\Barangay;
use App\Models\Citizen;
use App\Models\City;
use App\Models\Payout;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected static ?int $sort = 0;

    protected function getStats(): array
    {

        // 'active'
        // 'inactive'
        // 'waitlisted'
        // 'deceased'
        // 'double_entry'
        // 'pension'
        // 'transferred'
        // 'unlocated'
        // 'well_off'
        // 'unknown'
        // 'other'
        return [
            Stat::make('', Region::where('is_active', '1')->count())
                ->label('TOTAL')
                ->description('REGIONS')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('gray'),
            Stat::make('', Province::where('is_active', '1')->count())
                ->label('TOTAL')
                ->description('PROVINCES')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('gray'),
            Stat::make('', City::where('is_active', '1')->count())
                ->label('TOTAL')
                ->description('CITIES')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),
            Stat::make('', Barangay::where('is_active', '1')->count())
                ->label('TOTAL')
                ->description('BARANGAYS')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('gray'),
            Stat::make('Active', Citizen::all()->count())
                ->label('TOTAL')
                ->description('CITIZENS')
                ->color('info'),
            Stat::make('Active', Citizen::where('gender', 'male')->count() . ' ♂ / ' . Citizen::where('gender', 'female')->count() . ' ♀')
                ->label('TOTAL')
                ->description('MALE / FEMALE')
                ->color('warning'),
//            Stat::make('Active', Citizen::where('gender', 'male')->count())
//                ->label('TOTAL MALE')
//                ->description('CITIZENS')
//                ->descriptionIcon('heroicon-m-user')
//                ->color('primary'),
//            Stat::make('Active', Citizen::where('gender', 'female')->count())
//                ->label('TOTAL FEMALE')
//                ->description('CITIZENS')
//                ->descriptionIcon('heroicon-m-user')
//                ->color('primary'),
            Stat::make('', '₱ ' . Payout::where('status', 'paid')->sum('amount'))
                ->label('TOTAL PAID')
                ->description('CITIZEN PAYOUTS')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
//            Stat::make('Active', Citizen::where('citizen_status', 'active')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Inactive', Citizen::where('citizen_status', 'inactive')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Waitlisted', Citizen::where('citizen_status', 'waitlisted')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Deceased', Citizen::where('citizen_status', 'deceased')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Double Entry', Citizen::where('citizen_status', 'double_entry')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Pension', Citizen::where('citizen_status', 'pension')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Transferred', Citizen::where('citizen_status', 'transferred')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Unlocated', Citizen::where('citizen_status', 'unlocated')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Well Off', Citizen::where('citizen_status', 'well_off')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Unknowed', Citizen::where('citizen_status', 'unknowned')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
//            Stat::make('Other', Citizen::where('citizen_status', 'other')->count())
//                ->description('Citizens')
//                ->descriptionIcon('heroicon-m-arrow-path')
//                ->color('primary'),
        ];
    }
}
