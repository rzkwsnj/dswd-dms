<?php

namespace App\Filament\Resources\CitizenResource\RelationManagers;

use Akaunting\Money\Currency;
use App\Models\Citizen;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PayoutsRelationManager extends RelationManager
{
    protected static string $relationship = 'payouts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference')
                    ->default('PH-' . random_int(100000, 999999))
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Forms\Components\Select::make('payout_period_year')
                            ->searchable()
                            ->options(collect(range(date('Y'), date('Y') - 20))->mapWithKeys(fn ($item, $key) => [(string) $item => (string) $item]))
                            ->required()
                            ->native(false),

                Forms\Components\Select::make('payout_period_semester')
                    ->options([
                        '1st_semester' => '1st Semester',
                        '2nd_semester' => '2nd Semester',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->required(),

                Forms\Components\Select::make('currency')
                    ->options(collect(Currency::getCurrencies())->mapWithKeys(fn ($item, $key) => [$key => data_get($item, 'name')]))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('method')
                    ->options([
                        'credit_card' => 'Credit card',
                        'debit_card' => 'Debit card',
                        'bank_transfer' => 'Bank transfer',
                        'cash' => 'Cash',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('status')
                    ->options([
                        'paid' => 'PAID',
                        'unpaid' => 'UNPAID',
                        'pending' => 'PENDING',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->copyable()
                    ->copyMessage('Reference copied')
                    ->copyMessageDuration(1500)
                    ->searchable(),

                Tables\Columns\TextColumn::make('citizen_id')
                    ->label('Citizen')
                    ->formatStateUsing(fn ($record) => $record->citizen->first_name . ', ' . $record->citizen->first_name . ' ' . $record->citizen->middle_name . ' ' . $record->citizen->extra_name)
                    ->searchable(),

                Tables\Columns\TextColumn::make('payout_period_year')
                    ->label('Year')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payout_period_semester')
                    ->label('Semester')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->sortable()
                    ->money(fn ($record) => $record->currency),

                Tables\Columns\TextColumn::make('date')
                    ->searchable(),

                Tables\Columns\TextColumn::make('method')
                    ->formatStateUsing(fn ($state) => Str::upper($state)),

                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => Str::upper($state)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                ExportBulkAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                            ->withFilename('citizen - payouts - ' . date('Y-m-d') . ' - export'),
                    ]),
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->label('Payout Date')
                    ->getTitleFromRecordUsing(fn ($record) => Carbon::parse(data_get($record, 'date'))->format(Tables\Table::$defaultDateDisplayFormat))
                    ->collapsible(),
                Tables\Grouping\Group::make('status')
                    ->label('Payout Status')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'status'))
                    ->collapsible(),
                Tables\Grouping\Group::make('payout_period_year')
                    ->label('Payout Year')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'payout_period_year'))
                    ->collapsible(),
                Tables\Grouping\Group::make('payout_period_semester')
                    ->label('Payout Semester')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'payout_period_semester'))
                    ->collapsible(),
            ]);
    }
}
