<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Components;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Jadwal';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationBadgeTooltip = 'Total Jadwal';
    protected static ?string $navigationGroup = 'Kelola Jadwal & Lapangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Select::make('field_id')
                    ->relationship('field', 'name')
                    ->required()
                    ->label('Lapangan'),

                Components\DateTimePicker::make('start_time')
                    ->required()
                    ->label('Waktu Mulai'),

                Components\DateTimePicker::make('end_time')
                    ->required()
                    ->label('Waktu Selesai'),

                Components\Select::make('status')
                    ->options([
                        'available' => 'Tersedia',
                        'booked' => 'Dibooking',
                        'maintenance' => 'Maintenance'
                    ])
                    ->required()
                    ->default('available')
                    ->label('Status')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('field.name')
                    ->label('Lapangan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'booked',
                        'danger' => 'maintenance',
                    ])
                    ->label('Status')
            ])
            ->defaultSort('start_time', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('field')
                    ->relationship('field', 'name')
                    ->label('Lapangan'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Tersedia',
                        'booked' => 'Dibooking',
                        'maintenance' => 'Maintenance'
                    ]),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('generateSchedules')
                    ->label('Generate Jadwal Sebulan')
                    ->color('success')
                    ->icon('heroicon-o-calendar-days')
                    ->requiresConfirmation()
                    ->action(function () {
                        // Delete existing schedules
                        Schedule::truncate();

                        $fields = \App\Models\Field::all();
                        $startDate = Carbon::now()->startOfDay();
                        $endDate = $startDate->copy()->addMonth();
                        $schedules = [];
                        $generated = 0;

                        foreach ($fields as $field) {
                            $currentDate = $startDate->copy();

                            while ($currentDate->lt($endDate)) {
                                // Generate schedules from 8:00 to 22:00
                                for ($hour = 8; $hour < 24; $hour++) {
                                    $schedules[] = [
                                        'field_id' => $field->id,
                                        'start_time' => $currentDate->copy()->setHour($hour),
                                        'end_time' => $currentDate->copy()->setHour($hour + 1)->subSecond(),
                                        'status' => 'available',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];

                                    $generated++;

                                    // Insert in chunks to avoid memory issues
                                    if (count($schedules) >= 100) {
                                        Schedule::insert($schedules);
                                        $schedules = [];
                                    }
                                }
                                $currentDate->addDay();
                            }
                        }

                        // Insert remaining schedules
                        if (!empty($schedules)) {
                            Schedule::insert($schedules);
                        }

                        Notification::make()
                            ->title('Jadwal Berhasil Dibuat!')
                            ->body("Berhasil membuat {$generated} jadwal untuk bulan depan.")
                            ->success()
                            ->send();
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
