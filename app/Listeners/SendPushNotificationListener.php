<?php

namespace App\Listeners;

use App\Events\SendNotificationEvent;
use App\Services\FirebaseService;



// class SendPushNotificationListener
// {
//     public function __construct(
//         protected FirebaseService $firebase
//     ) {}

//     public function handle(SendNotificationEvent $event): void
//     {
//        [
//             'user' => $event->user->id,
//             'title' => $event->title
//         ];

//         $this->firebase->sendToUser(
//             $event->user,
//             $event->title,
//             $event->body,
//             $event->data
//         );
//     }

// }

class SendPushNotificationListener
{
    public function handle(SendNotificationEvent $event): void
    {
        if (!env('FIREBASE_ENABLED', false)) {
            return;
        }

        app(\App\Services\FirebaseService::class)
            ->sendToUser(
                $event->user,
                $event->title,
                $event->body,
                $event->data
            );
    }
}
