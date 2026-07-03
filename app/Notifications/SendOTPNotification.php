<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;

class SendOTPNotification extends Notification
{
    use Queueable,Notifiable;

    protected $otp;

    public function __construct()
    {
        $this->otp = rand(100000, 999999);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // تخزين الـ OTP في Cache
        Cache::put('otp_' . $notifiable->email, $this->otp, now()->addMinutes(10));

        return (new MailMessage)
            ->subject('Your OTP Code - Animal Rescue Platform')
            ->greeting('Hello ' . $notifiable->full_name)
            ->line('Your verification code is:')
            ->line('**' . $this->otp . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this code, please ignore this email.');
    }

    /**
     * Static method to verify OTP
     */
    public static function verifyOTP($email, $otp)
    {
        $cachedOtp = Cache::get('otp_' . $email);
        return $cachedOtp && $cachedOtp == $otp;
    }
}
