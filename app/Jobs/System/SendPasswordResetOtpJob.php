<?php

namespace App\Jobs\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPasswordResetOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $otp,
    ) {}

    public function handle(): void
    {
        Mail::raw(
            "Mã OTP đặt lại mật khẩu của bạn là: {$this->otp}. Mã có hiệu lực trong 10 phút.",
            function ($message) {
                $message->to($this->email)->subject('Mã OTP đặt lại mật khẩu');
            }
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Password reset OTP mail failed.', [
            'email' => $this->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
