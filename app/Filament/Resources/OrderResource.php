<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\UserMembership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Order';

    protected static ?string $pluralModelLabel = 'Orders';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_method')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('order_type')
                    ->label('Order Type')
                    ->getStateUsing(function (Payment $record): string {
                        $booking = Booking::where('order_id', $record->order_id)->first();
                        $membership = UserMembership::where('order_id', $record->order_id)->first();
                        
                        if ($booking) {
                            return 'Field Booking';
                        } elseif ($membership) {
                            return 'Membership';
                        }
                        return 'Unknown';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Field Booking' => 'info',
                        'Membership' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'cash' => 'Cash',
                    ]),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('order_id')
                                    ->label('Order ID')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('amount')
                                    ->label('Total Amount')
                                    ->money('IDR')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->badge(),
                                TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime(),
                            ]),
                    ]),

                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Customer Name')
                                    ->size(TextEntry\TextEntrySize::Large),
                                TextEntry::make('user.email')
                                    ->label('Email'),
                                TextEntry::make('user.phone')
                                    ->label('Phone'),
                                TextEntry::make('user.role')
                                    ->label('Role')
                                    ->badge(),
                            ]),
                    ]),

                Section::make('Order Details')
                    ->schema([
                        // Dynamic content based on order type
                        TextEntry::make('order_details')
                            ->label('')
                            ->html()
                            ->getStateUsing(function (Payment $record): string {
                                $booking = Booking::where('order_id', $record->order_id)->first();
                                $membership = UserMembership::where('order_id', $record->order_id)->first();
                                
                                if ($booking) {
                                    return self::renderBookingDetails($booking);
                                } elseif ($membership) {
                                    return self::renderMembershipDetails($membership);
                                }
                                
                                return '<div class="text-gray-500">No order details found</div>';
                            }),
                    ]),
            ]);
    }

    private static function renderBookingDetails(Booking $booking): string
    {
        $field = $booking->field;
        
        return "
        <div class='bg-blue-50 p-4 rounded-lg border border-blue-200'>
            <h3 class='text-lg font-semibold text-blue-800 mb-3'>Field Booking Details</h3>
            <div class='grid grid-cols-2 gap-4'>
                <div>
                    <p class='text-sm text-gray-600'>Field Name</p>
                    <p class='font-medium'>{$field->name}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Booking Date</p>
                    <p class='font-medium'>{$booking->booking_date->format('d M Y')}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Start Time</p>
                    <p class='font-medium'>{$booking->start_time->format('H:i')}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>End Time</p>
                    <p class='font-medium'>{$booking->end_time->format('H:i')}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Duration</p>
                    <p class='font-medium'>" . $booking->start_time->diffInHours($booking->end_time) . " hours</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Price</p>
                    <p class='font-medium'>Rp " . number_format($booking->price, 0, ',', '.') . "</p>
                </div>
            </div>
        </div>";
    }

    private static function renderMembershipDetails(UserMembership $membership): string
    {
        $membershipData = $membership->membership;
        $field = $membership->field;
        
        return "
        <div class='bg-green-50 p-4 rounded-lg border border-green-200'>
            <h3 class='text-lg font-semibold text-green-800 mb-3'>Membership Details</h3>
            <div class='grid grid-cols-2 gap-4'>
                <div>
                    <p class='text-sm text-gray-600'>Membership Name</p>
                    <p class='font-medium'>{$membershipData->name}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Field</p>
                    <p class='font-medium'>{$field->name}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Day of Week</p>
                    <p class='font-medium'>{$membership->day_of_week}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Booking Time</p>
                    <p class='font-medium'>{$membership->booking_time->format('H:i')}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Start Date</p>
                    <p class='font-medium'>{$membership->start_date->format('d M Y')}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>End Date</p>
                    <p class='font-medium'>{$membership->end_date->format('d M Y')}</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Duration</p>
                    <p class='font-medium'>{$membershipData->duration} days</p>
                </div>
                <div>
                    <p class='text-sm text-gray-600'>Price</p>
                    <p class='font-medium'>Rp " . number_format($membershipData->price, 0, ',', '.') . "</p>
                </div>
            </div>
        </div>";
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
