<?php

namespace App\Filament\Resources\CitizenResource\Pages;

use App\Filament\Resources\CitizenResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Resources\Components\Tab;

class ListCitizens extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CitizenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->fromForm()
                        ->withColumns([
                            Column::make('batch')->heading('batch'),
                            Column::make('control_no')->heading('control_no'),
                            Column::make('scid')->heading('scid'),
                            Column::make('identifier')->heading('identifier'),
                            Column::make('first_name')->heading('first_name'),
                            Column::make('middle_name')->heading('middle_name'),
                            Column::make('last_name')->heading('last_name'),
                            Column::make('extra_name')->heading('extra_name'),
                            Column::make('gender')->heading('gender'),
                            Column::make('birthday')->heading('birthday'),
                            Column::make('disability_status')->heading('disability_status'),
                            Column::make('ip_status')->heading('ip_status'),
                            Column::make('representative')->heading('representative'),
                            Column::make('correction_remarks')->heading('correction_remarks'),
                            Column::make('region_id')->heading('region_id'),
                            Column::make('province_id')->heading('province_id'),
                            Column::make('city_id')->heading('city_id'),
                            Column::make('barangay_id')->heading('barangay_id'),
                            Column::make('address')->heading('address'),
                            Column::make('extra_address')->heading('extra_address'),
                            Column::make('additional')->heading('additional'),
                            Column::make('citizen_status')->heading('citizen_status'),
                            Column::make('date_downloaded')->heading('date_downloaded'),
                            Column::make('food_status')->heading('food_status'),
                            Column::make('medicine_vitamin_status')->heading('medicine_vitamin_status'),
                            Column::make('medical_health_check_status')->heading('medical_health_check_status'),
                            Column::make('clothing_status')->heading('clothing_status'),
                            Column::make('utilities_status')->heading('utilities_status'),
                            Column::make('debit_payment_status')->heading('debit_payment_status'),
                            Column::make('livelihood_activities_status')->heading('livelihood_activities_status'),
                            Column::make('other_status')->heading('other_status'),
                            Column::make('replacement')->heading('replacement'),
                            Column::make('quarter_of_separation')->heading('quarter_of_separation'),
                            Column::make('detailed_remarks')->heading('detailed_remarks'),
                            Column::make('remarks')->heading('remarks'),
                        ])
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                        ->withFilename('citizens - ' . date('Y-m-d') . ' - export'),
                ]),
            Actions\CreateAction::make(),
            ImportAction::make()
                ->uniqueField('control_no')
                ->fields([
                    ImportField::make('batch'),
                    ImportField::make('control_no'),
                    ImportField::make('scid'),
                    ImportField::make('identifier'),
                    ImportField::make('first_name'),
                    ImportField::make('middle_name'),
                    ImportField::make('last_name'),
                    ImportField::make('extra_name'),
                    ImportField::make('gender'),
                    ImportField::make('birthday'),
                    ImportField::make('disability_status'),
                    ImportField::make('ip_status'),
                    ImportField::make('representative'),
                    ImportField::make('correction_remarks'),
                    ImportField::make('region_id'),
                    ImportField::make('province_id'),
                    ImportField::make('city_id'),
                    ImportField::make('barangay_id'),
                    ImportField::make('address'),
                    ImportField::make('extra_address'),
                    ImportField::make('additional'),
                    ImportField::make('citizen_status'),
                    ImportField::make('date_downloaded'),
                    ImportField::make('food_status'),
                    ImportField::make('medicine_vitamin_status'),
                    ImportField::make('medical_health_check_status'),
                    ImportField::make('clothing_status'),
                    ImportField::make('utilities_status'),
                    ImportField::make('debit_payment_status'),
                    ImportField::make('livelihood_activities_status'),
                    ImportField::make('other_status'),
                    ImportField::make('replacement'),
                    ImportField::make('quarter_of_separation'),
                    ImportField::make('detailed_remarks'),
                    ImportField::make('remarks'),
                ], columns:3)
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return CitizenResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'ACTIVE' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'active')),
            'INACTIVE' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'inactive')),
            'WAITLISTED' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'waitlisted')),
            'DECEASED' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'deceased')),
            'DOUBLE_ENTRY' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'double_entry')),
            'PENSION' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'pension')),
            'TRANSFERRED' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'transferred')),
            'UNLOCATED' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'unlocated')),
            'WELL_OFF' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'well_off')),
            'UNKNOWN' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'unknown')),
            'OTHER' => Tab::make()->query(fn ($query) => $query->where('citizen_status', 'other')),
        ];
    }
}
