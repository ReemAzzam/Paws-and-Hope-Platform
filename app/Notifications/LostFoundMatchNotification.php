<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class LostFoundMatchNotification extends Notification
{
    use Queueable;

    protected $matchData;

    public function __construct($matchData)
    {
        $this->matchData = $matchData;
    }

    public function via($notifiable)
    {
        return ['firebase'];   // + 'mail' إذا أردت
    }

    public function toFirebase($notifiable)
    {
        $message = CloudMessage::withTarget('token', $notifiable->fcm_token)
            ->withNotification([
                'title' => 'تم العثور على مطابقة!',
                'body'  => 'يوجد حيوان يشبه الذي أبلغت عنه: ' . $this->matchData['animal_name'],
            ])
            ->withData([
                'type' => 'match',
                'match_id' => $this->matchData['id']
            ]);

        Firebase::messaging()->send($message);
    }
}
