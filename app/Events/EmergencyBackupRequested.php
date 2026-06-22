<?php

namespace App\Events;

use App\Models\BackupRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyBackupRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $backupRequest;

    public function __construct(BackupRequest $backupRequest)
    {
        $this->backupRequest = $backupRequest;
    }

    public function broadcastOn()
    {
        return new Channel('volunteer-backup');
    }

    public function broadcastAs()
    {
        return 'backup.requested';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->backupRequest->id,
            'rescue_report_id' => $this->backupRequest->rescue_report_id,
            'volunteer_name' => $this->backupRequest->volunteer->user->full_name,
            'latitude' => $this->backupRequest->latitude,
            'longitude' => $this->backupRequest->longitude,
            'urgency_level' => $this->backupRequest->urgency_level,
            'reason' => $this->backupRequest->reason,
            'status' => $this->backupRequest->status,
            'created_at' => $this->backupRequest->created_at->toDateTimeString(),
        ];
    }
}