<?php

namespace App\Filament\Resources\ProvinceResource\Pages;

use App\Filament\Resources\ProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ManageProvinces extends ManageRecords
{
    protected static string $resource = ProvinceResource::class;

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
                    ->withFilename('provinces - ' . date('Y-m-d') . ' - export'),
            ]),
            Actions\CreateAction::make(),
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
