<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VolunteerLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reportId;
    public $latitude;
    public $longitude;

    public function __construct($reportId, $latitude, $longitude)
    {
        $this->reportId = $reportId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rescue-report.' . $this->reportId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'LocationUpdated';
    }
}