<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function send(string $email, string $purpose): OtpCode
    {
        OtpCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $otp = OtpCode::query()->create([
            'email' => $email,
            'code_authen' => (string) random_int(100000, 999999),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw(
            "Ma xac thuc TS Screen: {$otp->code_authen} (het han sau 10 phut)",
            function ($message) use ($email): void {
                $message->to($email)->subject('TS Screen OTP');
            }
        );

        return $otp;
    }

    public function consume(string $email, string $purpose, string $code): bool
    {
        $otp = OtpCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (! $otp || ! $otp->isValid($code)) {
            return false;
        }

        $otp->update(['used_at' => now()]);

        return true;
    }
}
