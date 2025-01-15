<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function sendResetCode(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = data_get($input, 'email');

        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordReset::query()
            ->where('email', $email)
            ->delete();

        PasswordReset::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'token' => Hash::make($token),
            'created_at' => now(),
            'attempts' => 0,
        ]);

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Reset code sent successfully']);
        }

        $user->notify(new ResetPasswordNotification($token, $request->email));

        return response()->json(['message' => 'Reset code sent successfully']);
    }

    public function verifyCode(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required', 'string'],
        ]);

        $email = data_get($input, 'email');

        $reset = PasswordReset::query()
            ->where('email', $email)
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if (! $reset) {
            throw ValidationException::withMessages([
                'token' => ['Reset code is invalid or has expired.'],
            ]);
        }

        $reset->increment('attempts');

        if ($reset->attempts > 5) {
            $reset->delete();
            throw ValidationException::withMessages([
                'token' => ['Too many invalid attempts. Please request a new code.'],
            ]);
        }

        if (! Hash::check($request->token, $reset->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid reset code.'],
            ]);
        }

        $tempToken = Hash::make(Str::random(32));
        $reset->update(['token' => $tempToken]);

        return response()->json([
            'message' => 'Code verified successfully',
            'temp_token' => $tempToken,
        ]);
    }

    public function reset(Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required',  'string'],
            'token' => ['required', 'string'],
        ]);

        $email = data_get($input, 'email');
        $password = data_get($input, 'password');
        $token = data_get($input, 'token');

        $reset = PasswordReset::query()
            ->where('email', $email)
            ->where('token', $token)
            ->where('created_at', '>', now()->subMinutes(15))
            ->first();

        if (! $reset) {
            throw ValidationException::withMessages([
                'email' => ['Invalid or expired reset token.'],
            ]);
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        $user->update([
            'password' => Hash::make($password),
        ]);

        $reset->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
