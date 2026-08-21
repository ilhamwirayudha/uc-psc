<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Models\CounselingRecord;

#[Signature('app:update-client-status')]
#[Description('Update client status based on counseling schedules')]
class UpdateClientStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // 1. Move to ongoing: scheduled_at <= now and end_time > now
        $ongoingRecords = CounselingRecord::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->where('end_time', '>', $now)
            ->get();

        foreach ($ongoingRecords as $record) {
            $client = $record->client;
            if ($client && in_array($client->status, ['assigned', 'needs_followup'])) {
                $client->update(['status' => 'ongoing']);
            }
        }

        // 2. Move to completed (CounselingRecord) and unpaid (Client): end_time <= now
        $completedRecords = CounselingRecord::where('status', 'scheduled')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($completedRecords as $record) {
            $record->update(['status' => 'completed']);
            
            $client = $record->client;
            if ($client && $client->status === 'ongoing') {
                $client->update(['status' => 'unpaid']);
            }
        }

        $this->info('Client statuses updated successfully.');
    }
}
