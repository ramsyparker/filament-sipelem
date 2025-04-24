<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserMembershipResource\Pages;
use App\Filament\Resources\UserMembershipResource\RelationManagers;
use App\Models\UserMembership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;

class UserMembershipResource extends Resource
{
    protected static ?string $model = UserMembership::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Membership User';
    protected static ?string $navigationGroup = 'Kelola Membership';
    protected static ?string $label = 'Membership User';
    protected static ?string $pluralLabel = 'Membership User';
    protected static ?string $navigationBadgeTooltip = 'Jumlah Membership User';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationBadge = 'Jumlah Membership User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name', function ($query) {
                        return $query->where('role', 'user');
                    })
                    ->required()
                    ->label('User')
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('membership_id')
                    ->relationship('membership', 'name')
                    ->required()
                    ->label('Paket Membership')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $membership = \App\Models\Membership::find($state);
                            if ($membership) {
                                $startDate = now()->startOfDay();
                                $endDate = $startDate->copy()->addDays($membership->duration - 1)->endOfDay();
                                
                                $set('start_date', $startDate);
                                $set('end_date', $endDate);
                                $set('status', 'active');
                            }
                        }
                    }),

                Forms\Components\Select::make('field_id')
                    ->relationship('field', 'name')
                    ->required()
                    ->label('Lapangan'),

                Forms\Components\Select::make('day_of_week')
                    ->options([
                        'Sunday' => 'Minggu',
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                    ])
                    ->required()
                    ->label('Hari'),

                Forms\Components\TimePicker::make('booking_time')
                    ->required()
                    ->label('Jam Booking')
                    ->format('H:i'),

                Forms\Components\Hidden::make('start_date'),
                Forms\Components\Hidden::make('end_date'),
                Forms\Components\Hidden::make('status')
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama User')
                    ->sortable(),

                Tables\Columns\TextColumn::make('membership.name')
                    ->label('Paket')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Sunday' => 'Minggu',
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('booking_time')
                    ->label('Jam')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sisa')
                    ->formatStateUsing(function ($state) {
                        $endDate = Carbon::parse($state);
                        $now = Carbon::now();
                        
                        if ($now->endOfDay()->greaterThan($endDate)) {
                            return '0 hari';
                        }
                        
                        $remainingDays = $now->startOfDay()->diffInDays($endDate) + 1;
                        return $remainingDays . ' hari';
                    })
                    ->sortable()
                    ->alignCenter()
                    ->color(function ($state) {
                        $endDate = Carbon::parse($state);
                        $now = Carbon::now();
                        $days = $now->startOfDay()->diffInDays($endDate) + 1;
                        
                        return $days <= 7 ? 'danger' : 'success';
                    }),
            ])
            ->filters([
                // filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUserMemberships::route('/'),
            'create' => Pages\CreateUserMembership::route('/create'),
            'edit' => Pages\EditUserMembership::route('/{record}/edit'),
        ];
    }
}
