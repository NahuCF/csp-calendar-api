<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\SendResetCodeRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\ResetPasswordSMS;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function sendResetCode(SendResetCodeRequest $request)
    {
        try {
            // Generate 6-digit code
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Delete any existing reset tokens for this user
            PasswordReset::where('email', $request->email)
                ->orWhere('phone', $request->phone)
                ->delete();

            // Create new reset token
            PasswordReset::create([
                'email' => $request->email,
                'phone' => $request->phone,
                'token' => Hash::make($token), // Store hashed token
                'created_at' => now(),
                'attempts' => 0,
            ]);

            $user = User::when($request->has('email'), function ($query) use ($request) {
                return $query->where('email', $request->email);
            }, function ($query) use ($request) {
                return $query->where('phone', $request->phone);
            })->first();

            $notification = $request->has('email')
                ? new ResetPasswordNotification($token, $request->email)
                : new ResetPasswordSMS($token, $request->phone);

            $user->notify($notification);

            return response()->json(['message' => 'Reset code sent successfully']);

        } catch (\Exception $e) {
            Log::error('Failed to send reset code', [
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to send reset code. Please try again later.',
            ], 500);
        }
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $reset = PasswordReset::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('phone', $request->phone);
        })
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if (! $reset) {
            throw ValidationException::withMessages([
                'token' => ['Reset code is invalid or has expired.'],
            ]);
        }

        // Increment attempts
        $reset->increment('attempts');

        // Check attempts (limit to 5)
        if ($reset->attempts > 5) {
            $reset->delete();
            throw ValidationException::withMessages([
                'token' => ['Too many invalid attempts. Please request a new code.'],
            ]);
        }

        // Verify token
        if (! Hash::check($request->token, $reset->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid reset code.'],
            ]);
        }

        // Generate a temporary token for the password reset form
        $tempToken = Hash::make(Str::random(32));
        $reset->update(['token' => $tempToken]);

        return response()->json([
            'message' => 'Code verified successfully',
            'temp_token' => $tempToken,
        ]);
    }

    public function reset(ResetRequest $request)
    {
        $reset = PasswordReset::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('phone', $request->phone);
        })
            ->where('token', $request->token)
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if (! $reset) {
            throw ValidationException::withMessages([
                'email' => ['Invalid or expired reset token.'],
            ]);
        }

        $user = User::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $reset->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
