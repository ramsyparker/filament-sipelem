<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMembership;
use App\Models\Schedule;
use Carbon\Carbon;

class GenerateMembershipSchedules extends Command
{
    protected $signature = 'memberships:generate-schedules';
    protected $description = 'Generate schedules for active memberships';

    public function handle()
    {
        $memberships = UserMembership::where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->get();

        foreach ($memberships as $membership) {
            $nextDate = Carbon::parse("next {$membership->day_of_week}")
                ->setTimeFromTimeString($membership->booking_time);

            if ($nextDate->between($membership->start_date, $membership->end_date)) {
                Schedule::create([
                    'field_id' => $membership->field_id,
                    'start_time' => $nextDate,
                    'end_time' => $nextDate->copy()->addHour(),
                    'status' => 'booked',
                    'user_id' => $membership->user_id
                ]);
            }
        }

        $this->info('Membership schedules generated successfully!');
    }
}