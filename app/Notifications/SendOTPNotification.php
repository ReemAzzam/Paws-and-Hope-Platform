<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;

class SendOTPNotification extends Notification
{
    use Queueable;

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
        Cache::put('otp_' . $notifiable->email, $this->otp, now()->addMinutes(15));


        return (new MailMessage)
    ->subject('Paws & Hope - Verification Code')
    ->greeting('Hello ' . ($notifiable->full_name ?? '!'))
    ->line('Your OTP code for email verification is:')
    ->line($this->otp)
    ->line('This code will expire in 15 minutes.')
    ->line('If you did not request this code, please ignore this message.')
    ->salutation('Best regards, The Paws & Hope Team');
}}
