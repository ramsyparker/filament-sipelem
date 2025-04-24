<?php

namespace App\Filament\Resources\UserMembershipResource\Pages;

use App\Filament\Resources\UserMembershipResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Models\Booking;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class CreateUserMembership extends CreateRecord
{
    protected static string $resource = UserMembershipResource::class;

    protected function afterCreate(): void
    {
        // Update user role to member
        $user = User::find($this->record->user_id);
        if ($user && $user->role === 'user') {
            $user->update(['role' => 'member']);
        }

        // Create bookings and update schedules
        $startDate = Carbon::parse($this->record->start_date);
        $endDate = Carbon::parse($this->record->end_date);
        $dayOfWeek = $this->record->day_of_week;
        $bookingTime = Carbon::parse($this->record->booking_time);
        
        $currentDate = $startDate->copy();
        $createdBookings = 0;

        while ($currentDate->lte($endDate)) {
            if ($currentDate->format('l') === $dayOfWeek) {
                $startDateTime = $currentDate->copy()->setTimeFromTimeString($bookingTime->format('H:i:s'));
                $endDateTime = $startDateTime->copy()->addHour();

                // Create booking
                $booking = Booking::create([
                    'user_id' => $this->record->user_id,
                    'field_id' => $this->record->field_id,
                    'booking_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $bookingTime->format('H:i:s'),
                    'end_time' => $endDateTime->format('H:i:s'),
                    'status' => 'confirmed',
                    'price' => 0,
                ]);

                // Update corresponding schedule
                Schedule::where('field_id', $this->record->field_id)
                    ->whereDate('start_time', $currentDate->format('Y-m-d'))
                    ->whereTime('start_time', $bookingTime->format('H:i:s'))
                    ->whereTime('end_time', $endDateTime->format('H:i:s'))
                    ->update(['status' => 'booked']);

                $createdBookings++;
            }
            $currentDate->addDay();
        }

        Notification::make()
            ->title('Membership berhasil dibuat')
            ->body("Berhasil membuat $createdBookings booking untuk hari {$this->record->day_of_week} jam {$bookingTime->format('H:i')}")
            ->success()
            ->send();
    }
}
