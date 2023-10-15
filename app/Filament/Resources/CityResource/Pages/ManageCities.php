<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ManageCities extends ManageRecords
{
    protected static string $resource = CityResource::class;
    
    protected static ?string $title = "Municipalities";

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exports([
                ExcelExport::make()
                    ->fromForm()
                    ->only([
                        'name',
                    ])
                    ->withColumns([
                        Column::make('name')->heading('name'),
                    ])
                    ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                    ->withFilename('cities - ' . date('Y-m-d') . ' - export'),
            ]),
            Actions\CreateAction::make()
                ->label("New Municipality"),
            ImportAction::make()
                ->uniqueField('name')
                ->fields([
                    ImportField::make('name')
                        ->label('Name')
                        ->required(),
                ], columns:1)->mutateBeforeCreate(function($row) {
                    $row['slug'] = Str::slug($row['name']);
                    return $row;
                })
        ];
    }

}
