<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Handle a password reset request.
     */
    public function requestReset(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'email' => 'required|email|exists:tb_staff_users,email',
        ]);

        $email = $request->input('email');

        // Generate a secure token
        $token = Str::random(60);

        // Insert the token into the password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        // Prepare the password reset link
        $resetLink = url("/password-reset?token={$token}&email={$email}");

        // Send the password reset email
        Mail::send('emails.password_reset', ['resetLink' => $resetLink], function ($message) use ($email) {
            $message->to($email)
                ->subject('Password Reset Request');
        });

        return response()->json([
            'message' => 'Password reset link has been sent to your email.',
        ], 200);
    }

    /**
     * Handle the password resetting.
     */
    public function resetPassword(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'email' => 'required|email|exists:tb_staff_users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $token = $request->input('token');
        $newPassword = $request->input('password');

        // Retrieve the password reset record
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'error' => 'Invalid password reset request.',
            ], 400);
        }

        // Check if the token is valid and not expired (e.g., 60 minutes)
        if (!Hash::check($token, $passwordReset->token)) {
            return response()->json([
                'error' => 'Invalid or expired token.',
            ], 400);
        }

        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return response()->json([
                'error' => 'Token has expired.',
            ], 400);
        }

        // Update the user's password
        $user = StaffUser::where('email', $email)->first();
        $user->password = Hash::make($newPassword);
        $user->save();

        // Delete the password reset token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ], 200);
    }
}
