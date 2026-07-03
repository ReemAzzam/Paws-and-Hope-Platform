<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\WebPushFcmOptions;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(
                base_path(env('FIREBASE_CREDENTIALS'))
            );

        $this->messaging = $factory->createMessaging();
    }

    /**
     * إرسال إشعار إلى Token واحد
     */
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): bool {
        try {

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(
                    Notification::create($title, $body)
                )
                ->withWebPushConfig(
                    WebPushConfig::fromArray([
                        'notification' => [
                            'icon' => '/logo.png',
                        ],
                        'fcm_options' => [
                            'link' => '/'
                        ]
                    ])
                )
                ->withData($data);

            $this->messaging->send($message);

            return true;

        } catch (MessagingException|FirebaseException $e) {

            report($e);

            return false;
        }
    }

    /**
     * إرسال إشعار لكل أجهزة المستخدم
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): void {

        $tokens = $user->fcmTokens()->pluck('token');

        foreach ($tokens as $token) {

            $this->sendToToken(
                $token,
                $title,
                $body,
                $data
            );
        }
    }
}
