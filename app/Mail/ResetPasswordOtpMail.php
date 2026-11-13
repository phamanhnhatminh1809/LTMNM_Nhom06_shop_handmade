<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $userName = 'Quý khách',
        public string $purpose = 'reset_password'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'register'
            ? '[Shop Handmade] Mã xác thực OTP đăng ký tài khoản'
            : '[Shop Handmade] Mã xác thực OTP khôi phục mật khẩu';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
