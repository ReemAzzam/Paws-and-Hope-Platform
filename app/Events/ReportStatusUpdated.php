<?php

namespace App\Events;

use App\Models\RescueReport;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reportId;
    public $status;
    public $statusTimeline;

    public function __construct(RescueReport $report)
    {
        $this->reportId = $report->id;
        $this->status = $report->status;
        
        $this->statusTimeline = [
            'reported'   => true,
            'dispatched' => in_array($report->status, ['dispatched', 'on_site', 'in_clinic', 'resolved']),
            'on_site'    => in_array($report->status, ['on_site', 'in_clinic', 'resolved']),
            'in_clinic'  => in_array($report->status, ['in_clinic', 'resolved']),
            'resolved'   => $report->status === 'resolved',
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rescue-report.' . $this->reportId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'StatusUpdated';
    }
}