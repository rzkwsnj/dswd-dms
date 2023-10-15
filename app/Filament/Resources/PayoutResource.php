<?php

namespace App\Filament\Resources;

use Akaunting\Money\Currency;
use App\Filament\Resources\PayoutResource\Pages;
use App\Filament\Resources\PayoutResource\RelationManagers;
use App\Filament\Resources\PayoutResource\Widgets\CitizenPayoutStats;
use App\Models\Payout;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PayoutResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Payout::class;

    protected static ?string $slug = 'modules/payouts';

    protected static ?string $navigationGroup = 'Modules';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->default('PH-' . random_int(100000, 999999))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpan('full'),
                        Forms\Components\Select::make('citizen_id')
                            ->relationship(
                                name: 'citizen',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('last_name')->orderBy('first_name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->last_name}, {$record->first_name} {$record->middle_name} {$record->extra_name}")
                            ->searchable(['control_no', 'first_name', 'middle_name', 'last_name', 'extra_name'])
                            ->preload()
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

                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->copyable()
                    ->copyMessage('Reference copied')
                    ->copyMessageDuration(1500)
                    ->searchable(),

                Tables\Columns\TextColumn::make('citizen.first_name')
                    ->label('Citizen')
                    ->formatStateUsing(fn ($record) => $record->citizen->first_name . ', ' . $record->citizen->first_name . ' ' . $record->citizen->middle_name . ' ' . $record->citizen->extra_name)
                    ->searchable(),

//                Tables\Columns\TextColumn::make('region')
//                    ->label('Region')
//                    ->formatStateUsing(fn ($record) => $record->citizen->region_id)
//                    ->searchable(),

                Tables\Columns\TextColumn::make('payout_period_year')
                    ->label('Year')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payout_period_semester')
                    ->label('Semester')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->sortable()
                    ->money(fn ($record) => $record->currency)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(env('APP_DEFAULT_CURRENCY')),
                    ]),

                Tables\Columns\TextColumn::make('date')
                    ->searchable(),

                Tables\Columns\TextColumn::make('method')
                    ->formatStateUsing(fn ($state) => Str::upper($state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'danger',
                        'PENDING' => 'warning'
                    })
                    ->getStateUsing( function (Model $record){
                        return strtoupper($record->status);
                    }),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CitizenPayoutStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayouts::route('/'),
            'create' => Pages\CreatePayout::route('/create'),
            'edit' => Pages\EditPayout::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'reorder',
            'force_delete',
            'force_delete_any'
        ];
    }
}
