<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class SendPasswordResetOTPNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        // إنشاء OTP عشوائي
        $this->otp = rand(100000, 999999);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // تخزين OTP في Cache لمدة 10 دقائق
        Cache::put('reset_otp_' . $notifiable->email, $this->otp, now()->addMinutes(10));

        return (new MailMessage)
            ->subject('Password Reset OTP')
            ->line('Your OTP code for resetting your password is:')
            ->line($this->otp)
            ->line('This code will expire in 10 minutes.');
    }
}
