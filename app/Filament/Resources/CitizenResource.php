<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitizenResource\Pages;
use App\Filament\Resources\CitizenResource\RelationManagers;
use App\Filament\Resources\CitizenResource\Widgets\CitizenStats;
use App\Models\Barangay;
use App\Models\Citizen;
use App\Models\City;
use App\Models\Payout;
use App\Models\Province;
use App\Models\Region;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class CitizenResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Citizen::class;

    protected static ?string $recordTitleAttribute = 'control_no';

    protected static ?string $slug = 'modules/citizens';

    protected static ?string $navigationGroup = 'Modules';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Personal Info')
                            ->collapsible()
                            ->collapsed()
                            ->schema(static::getFormSchema('personal_info')),

                        Forms\Components\Section::make('Address')
                            ->collapsible()
                            ->collapsed()
                            ->schema(static::getFormSchema('address')),

                        Forms\Components\Section::make('Utilizations of Social Pension')
                            ->collapsible()
                            ->collapsed()
                            ->schema(static::getFormSchema('utilizations')),
                    ])
                    ->columnSpan(['lg' => fn (?Citizen $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('citizen_status')
                            ->label('STATUS')
                            ->hidden(fn (?Citizen $record) => $record === null )
                            ->placeholder('Select Status')
                            ->options([
                                'active' => 'ACTIVE',
                                'inactive' => 'INACTIVE',
                                'waitlisted' => 'WAITLISTED',
                                'deceased' => 'DECEASED',
                                'double_entry' => 'DOUBLE ENTRY',
                                'pension' => 'PENSION',
                                'transferred' => 'TRANSFERRED',
                                'unlocated' => 'UNLOCATED',
                                'well_off' => 'WELL OFF',
                                'unknown' => 'UNKNOWN',
                                'other' => 'OTHER'
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\DatePicker::make('date_downloaded')
                            ->label('DATE DOWNLOADED')
                            ->hidden(fn (?Citizen $record) => $record === null ),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (Citizen $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (Citizen $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Citizen $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ImageColumn::make('photo')
                //     ->label('Avatar')
                //     ->defaultImageUrl(fn (Model $record): string => url(\Filament\Facades\Filament::getUserAvatarUrl($record)))
                //     ->circular(),
                Tables\Columns\TextColumn::make('batch')
                    ->sortable(),
                Tables\Columns\TextColumn::make('control_no')
                    ->label('Control NO.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Full Name')
                    ->searchable(['first_name', 'middle_name', 'last_name', 'extra_name'])
                    ->sortable()
                    ->getStateUsing( function (Model $record){
                        return $record->last_name . ', ' . $record->first_name . ' ' . $record->middle_name . ' ' . $record->extra_name;
                    }),
                Tables\Columns\TextColumn::make('gender')
                     ->searchable()
                     ->sortable()
                     ->getStateUsing( function (Model $record){
                        return strtoupper($record->gender);
                     }),
                Tables\Columns\TextColumn::make('birthday')
                     ->label('Age')
                     ->searchable()
                     ->alignCenter()
                     ->getStateUsing( function (Model $record){
                         return Carbon::createFromDate($record->birthday)->diff(Carbon::now())->format('%y');
                      })
                      ->description('Years'),
                Tables\Columns\TextColumn::make('regions.name')
                    ->label('Region')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('provinces.name')
                    ->label('Province')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cities.name')
                    ->label('Municipality')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barangays.name')
                    ->label('Barangay')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('citizen_status')
                    ->label('Status')
                    ->color(fn (string $state): string => match (strtoupper($state)) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        'WAITLISTED' => 'warning',
                        'DECEASED' => 'gray',
                        'DOUBLE_ENTRY' => 'danger',
                        'PENSION' => 'gray',
                        'TRANSFERRED' => 'info',
                        'UNLOCATED' => 'warning',
                        'WELL_OFF' => 'warning',
                        'UNKNOWN' => 'gray',
                        'OTHER' => 'gray'
                    })
                    ->getStateUsing( function (Model $record){
                        return strtoupper($record->citizen_status);
                     }),

                TextColumn::make('payouts_count')
                    ->label('Total Payouts')
                    ->alignCenter()
//                    ->listWithLineBreaks()
//                    ->limitList(2)
                    // ->description(fn (Model $record): string => $record->payouts[0]->reference, position: 'above')
                    ->counts('payouts')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Citizen registered from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Citizen registered until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (Citizen $record) => $record->payouts()->count() > 0 ? Notification::make()->title('There is a citizen payout relationship, please delete the payout data first!')->warning()->send() : $record->delete()),
                    Tables\Actions\ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (Citizen $record) => $record->payouts()->count() > 0 ? Notification::make()->title('There is a citizen payout relationship, please delete the payout data first!')->warning()->send() : $record->forceDelete()),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->groupedBulkActions([
                ExportBulkAction::make()
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
                Tables\Actions\RestoreBulkAction::make(),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Created Date')
                    ->getTitleFromRecordUsing(fn ($record) => Carbon::parse(data_get($record, 'created_at'))->format(Tables\Table::$defaultDateDisplayFormat))
                    ->collapsible(),
                Tables\Grouping\Group::make('region_id')
                    ->label('Regions')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'regions.name'))
                    ->collapsible(),
                Tables\Grouping\Group::make('province_id')
                    ->label('Provinces')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'provinces.name'))
                    ->collapsible(),
                Tables\Grouping\Group::make('city_id')
                    ->label('Cities')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'cities.name'))
                    ->collapsible(),
                Tables\Grouping\Group::make('barangay_id')
                    ->label('Barangays')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'barangays.name'))
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PayoutsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CitizenStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCitizens::route('/'),
            'create' => Pages\CreateCitizen::route('/create'),
            'edit' => Pages\EditCitizen::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['control_no', 'last_name', 'first_name', 'middle_name', 'extra_name', 'regions.name', 'provinces.name', 'cities.name', 'barangays.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
{
    return [
        'Full Name' => $record->last_name . ', ' . $record->first_name . ' ' . $record->middle_name . ' ' . $record->extra_name,
        'Region' => $record->regions->name,
        'Province' => $record->provinces->name,
        'City' => $record->cities->name,
        'Barangay' => $record->barangays->name,
    ];
}

    public static function getNavigationBadge(): ?string
    {
        return static::$model::where('citizen_status', 'active')->count();
    }

    public static function getFormSchema(string $section = null): array
    {
        if ($section === 'personal_info') {
            return [
                // Forms\Components\Section::make('Photo')
                //     ->schema([
                //         FileUpload::make('photo')
                //             ->image()
                //             ->avatar()
                //             ->directory('avatars')
                //             ->visibility('private')
                //             ->rules(['nullable', 'mimes:jpg,jpeg,png', 'max:1024'])
                //             ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, callable $get) : string {
                //                 return (string) str(Str::random(5) . '-' . $get('last_name') . '.' . $file->getClientOriginalExtension())->prepend('avatar-');
                //             }),
                //         ])
                //     ->collapsible()
                //     ->columns(2),

                Forms\Components\TextInput::make('first_name')
                    ->maxValue(100)
                    ->required(),

                Forms\Components\TextInput::make('middle_name')
                    ->maxValue(100)
                    ->required(),

                Forms\Components\TextInput::make('last_name')
                    ->maxValue(100)
                    ->required(),

                Forms\Components\TextInput::make('extra_name')
                    ->maxValue(100)
                    ->required(),

                Forms\Components\Select::make('gender')
                    ->label('SEX')
                    ->placeholder('Select Sex')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('birthday')
                    ->label('Birth Date')
                    ->maxDate('today')
                    ->required(),

                Forms\Components\TextInput::make('disability_status')
                    ->label('Disability')
                    ->maxValue(100),

                Forms\Components\TextInput::make('ip_status')
                    ->label('IP')
                    ->maxValue(100),

                Forms\Components\TextInput::make('representative')
                    ->maxValue(200)
                    ->columnSpan('full'),

                Forms\Components\MarkdownEditor::make('correction_remarks')
                    ->label('Correction Remarks')
                    ->columnSpan('full'),

            ];
        }

        if ($section === 'address') {
            return [
                Forms\Components\Select::make('region_id')
                    ->relationship('regions', 'name',
                    fn (Builder $query) => $query->where('is_active', '1'))
                    ->searchable(['name'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Region::class, 'slug', ignoreRecord: true),
                    ]),

                Forms\Components\Select::make('province_id')
                    ->relationship('provinces', 'name',
                    fn (Builder $query) => $query->where('is_active', '1'))
                    ->searchable(['name'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Province::class, 'slug', ignoreRecord: true),
                    ]),

                Forms\Components\Select::make('city_id')
                    ->label('Municipalities')
                    ->relationship('cities', 'name',
                    fn (Builder $query) => $query->where('is_active', '1'))
                    ->searchable(['name'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(City::class, 'slug', ignoreRecord: true),
                    ]),

                Forms\Components\Select::make('barangay_id')
                    ->relationship('barangays', 'name',
                    fn (Builder $query) => $query->where('is_active', '1'))
                    ->searchable(['name'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Barangay::class, 'slug', ignoreRecord: true),
                    ]),

                Forms\Components\MarkdownEditor::make('address')
                    ->label('Permanent (House No./Zone/Purok/Sitio)')
                    ->columnSpan('full'),

                Forms\Components\MarkdownEditor::make('extra_address')
                    ->label('Permanent (Street)')
                    ->columnSpan('full'),

            ];
        }

        if ($section === 'utilizations') {
            return [
                Section::make('')
                    ->columns(2)
                    ->schema([
                            Forms\Components\TextInput::make('food_status')
                                ->label('FOOD')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('medicine_vitamin_status')
                                ->label('MEDICINE/VITAMINS')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('medical_health_check_status')
                                ->label('HEALTH CHECK-UP & OTHER HOSPITAL/MEDICAL SERVICES')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('clothing_status')
                                ->label('CLOTHINGS')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('utilities_status')
                                ->label('UTILITIES')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('debit_payment_status')
                                ->label('DEBIT PAYMENT')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('livelihood_activities_status')
                                ->label('LIVELIHOOD/ENTERPRENEURIAL ACTIVITIES')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('other_status')
                                ->label('OTHERS')
                                ->maxValue(50),

                            Forms\Components\TextInput::make('replacement')
                                ->label('REPLACEMENT W/')
                                ->maxValue(200),

                            Forms\Components\TextInput::make('quarter_of_separation')
                                ->label('QUARTER OF SEPARATIONS')
                                ->maxValue(200),

                            Forms\Components\MarkdownEditor::make('detailed_remarks')
                                ->label('DETAILED REMARKS'),

                            Forms\Components\MarkdownEditor::make('remarks')
                                ->label('REMARKS'),
                    ]),
                        Forms\Components\MarkdownEditor::make('additional')
                            ->label('ADDITIONAL'),

                        Forms\Components\Select::make('citizen_status')
                            ->label('STATUS')
                            ->placeholder('Select Status')
                            ->hidden(fn (?Citizen $record) => $record !== null )
                            ->options([
                                'active' => 'ACTIVE',
                                'inactive' => 'INACTIVE',
                                'waitlisted' => 'WAITLISTED',
                                'deceased' => 'DECEASED',
                                'double_entry' => 'DOUBLE ENTRY',
                                'pension' => 'PENSION',
                                'transferred' => 'TRANSFERRED',
                                'unlocated' => 'UNLOCATED',
                                'well_off' => 'WELL OFF',
                                'unknown' => 'UNKNOWN',
                                'other' => 'OTHER'
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\DatePicker::make('date_downloaded')
                            ->label('DATE DOWNLOADED')
                            ->hidden(fn (?Citizen $record) => $record !== null ),
            ];
        }

        return [
            Forms\Components\TextInput::make('batch')
                ->maxValue(50)
                ->required(),

            Forms\Components\TextInput::make('control_no')
                ->label('Control Number')
                ->default('PH-' . random_int(100000, 999999))
                ->disabled()
                ->dehydrated()
                ->required(),

            Forms\Components\TextInput::make('scid')
                ->label('SCID')
                ->maxValue(100),

            Forms\Components\TextInput::make('identifier')
                ->maxValue(100)
                ->required(),
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
