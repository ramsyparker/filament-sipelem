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
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Jadwal';
    protected static ?string $navigationGroup = 'Master Data';

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
            ->headerActions([
                \Filament\Tables\Actions\Action::make('generateSchedules')
                    ->label('Generate Jadwal Sebulan')
                    ->action(function () {
                        // Delete existing schedules first
                        Schedule::query()->delete();
                        
                        $fields = \App\Models\Field::all();
                        $startDate = Carbon::now()->startOfDay();
                        $daysInMonth = $startDate->daysInMonth;
                        $generated = 0;
                        
                        foreach ($fields as $field) {
                            for ($day = 0; $day < $daysInMonth; $day++) {
                                $currentDate = $startDate->copy()->addDays($day);
                                
                                // Generate dari jam 8 pagi sampai 23 malam
                                for ($hour = 8; $hour < 23; $hour++) {
                                    Schedule::create([
                                        'field_id' => $field->id,
                                        'start_time' => $currentDate->copy()->setHour($hour)->setMinute(0),
                                        'end_time' => $currentDate->copy()->setHour($hour + 1)->setMinute(0),
                                        'status' => 'available'
                                    ]);
                                    $generated++;
                                }
                            }
                        }

                        Notification::make()
                            ->title('Jadwal Berhasil Dibuat!')
                            ->body("Berhasil membuat {$generated} jadwal untuk semua lapangan selama sebulan.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generate Jadwal Otomatis')
                    ->modalDescription('Ini akan menghapus jadwal yang ada dan membuat jadwal baru untuk semua lapangan selama 1 bulan kedepan. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Generate!')
                    ->color('success')
                    ->icon('heroicon-o-calendar')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('field.name')
                    ->label('Lapangan')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime('d M Y H:i')
                    ->label('Waktu Mulai')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime('d M Y H:i')
                    ->label('Waktu Selesai')
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

                Tables\Filters\Filter::make('date')
                    ->form([
                        Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal'),
                        Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Dari tanggal ' . Carbon::parse($data['start_date'])->format('d M Y');
                        }
                        
                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Sampai tanggal ' . Carbon::parse($data['end_date'])->format('d M Y');
                        }
                        
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
