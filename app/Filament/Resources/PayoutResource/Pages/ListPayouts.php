<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListPayouts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exports([
                ExcelExport::make()
                    ->fromTable()
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                    ->withFilename('citizen - payouts - ' . date('Y-m-d') . ' - export'),
            ]),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return PayoutResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'PAID' => Tab::make()->query(fn ($query) => $query->where('status', 'paid')),
            'UNPAID' => Tab::make()->query(fn ($query) => $query->where('status', 'unpaid')),
            'PENDING' => Tab::make()->query(fn ($query) => $query->where('status', 'pending')),
        ];
    }
}
