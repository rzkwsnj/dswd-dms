<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CitizenResource;
use App\Models\Citizen;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Akaunting\Money\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class LatestCitizenDash extends BaseWidget
{
    protected static ?string $heading = 'Latest Citizens';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(CitizenResource::getEloquentQuery()->where('deleted_at', NULL))
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
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
            ->actions([
                Tables\Actions\Action::make('open')
                    ->url(fn (Citizen $record): string => CitizenResource::getUrl('edit', ['record' => $record])),
            ]);
    }

}
