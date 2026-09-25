<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\Booking;

#[Signature('app:update-client-status')]
#[Description('Update client status based on booking schedules')]
class UpdateClientStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        $bookings = Booking::whereDate('tanggal_dijadwalkan', '<=', $today)
            ->whereIn('status', ['baru', 'lanjutan'])
            ->with('client')
            ->get();

        foreach ($bookings as $booking) {
            $client = $booking->client;
            if ($client && in_array($client->status, ['assigned', 'needs_followup'])) {
                $client->update(['status' => 'ongoing']);
            }
        }

        $this->info('Client statuses updated successfully.');
    }
}
