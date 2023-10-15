<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangayResource\Pages;
use App\Filament\Resources\BarangayResource\RelationManagers;
use App\Models\Barangay;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class BarangayResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Barangay::class;

    protected static ?string $slug = 'master-data/barangays';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                            Forms\Components\TextInput::make('slug')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->unique(Barangay::class, 'slug', ignoreRecord: true),
                        ]),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Visible to users.')
                        ->default(true),
                ])
                ->columnSpan(['lg' => fn (?Barangay $record) => $record === null ? 3 : 2]),
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn (Barangay $record): ?string => $record->created_at?->diffForHumans()),

                    Forms\Components\Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn (Barangay $record): ?string => $record->updated_at?->diffForHumans()),
                ])
                ->columnSpan(['lg' => 1])
                ->hidden(fn (?Barangay $record) => $record === null),
        ])
        ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visibility')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(fn (Barangay $record) => $record->citizens()->count() > 0 ? Notification::make()->title('There is a citizen relationship, please delete the related data first!')->warning()->send() : $record->delete()),
            ])
            ->groupedBulkActions([
                ExportBulkAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromForm()
                            ->only([
                                'name',
                            ])
                            ->withColumns([
                                Column::make('name')->heading('name'),
                            ])
                            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                            ->withFilename('barangays - ' . date('Y-m-d') . ' - export'),
                    ]),
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('sort')
            ->reorderable('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBarangays::route('/'),
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
