<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Models\MetaServer;
use App\Models\StaffLoginAttempt;
use App\Models\StaffUser;
use App\Models\UserAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Attempt to find the user by email
        $user = StaffUser::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found.',
            ], 404);
        }

        // Check if the user is active and not deleted
        if (!$user->active || $user->is_deleted) {
            return response()->json([
                'error' => 'User account is inactive or has been deleted.',
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->input('password'), $user->password)) {
            return $this->handleFailedLogin($user, $request);
        }

        // Check if the user is locked out
        if ($this->isLockedOut($user)) {
            $timeDifference = $this->getLockoutTimeDifference($user);
            return response()->json([
                'error' => "Too many login attempts. Please try again in $timeDifference seconds.",
                'timerSec' => $timeDifference,
            ], 429); // 429 Too Many Requests
        }

        // Clear failed login attempts on successful login
        StaffLoginAttempt::where('staff_id', $user->id)->delete();

        // Create a new token
        $token = $user->createToken('admin_token')->plainTextToken;
        $tokenParts = explode("|", $token);
        $numericIdentifier = $tokenParts[0];

        if ($numericIdentifier) {
            $staffIp = $request->ip();
            $staffOrgIp = $request->header('X-Forwarded-For');

            // Update the IP address for the token in the 'personal_access_tokens' table
            DB::table('personal_access_tokens')
                ->where('id', $numericIdentifier)
                ->update(['ip_address' => $staffIp]);

            // Log the token usage
            UserAccessLog::create([
                'user_type' => get_class($user),
                'type_of_user' => 'StaffUser',
                'personal_access_token_id' => $numericIdentifier,
                'user_id' => $user->id,
                'ip_address' => $staffIp,
                'asn_organization' => $staffOrgIp,
            ]);
        }

        // Define user abilities
        $userAbilityRules = [
            [
                "action" => "manage",
                "subject" => "all",
            ],
        ];

        $baseUrl = ''; // Define as per your needs
        $params = Hash::make($user->email); // Hash parameters as needed
        $metaServer = MetaServer::where('manager_id', $user->id)->where('status', 1)->first();
        return response()->json([
            'user' => $user,
            'token' => $token,
            'abilityRules' => $userAbilityRules,
            'baseUrl' => $baseUrl,
            'server_info' => $metaServer,
            'message' => 'Login Successfully',
            'params' => $params
        ], 200);
    }


    public function pluginLogin(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Generate a Bearer token for the user
        $token = $user->createToken('plugin_auth_token')->plainTextToken;

        $userId = Auth::id();

        // Find the entry in tb_meta_servers where manager_id matches the authenticated user ID and status is 1
        $metaServer = MetaServer::where('manager_id', $userId)->where('status', 1)->first();

        // If no entry is found, return an error response
        if (!$metaServer) {
            return response()->json([
                'success' => false,
                'message' => 'Couldnt find any mapped servers !',
            ], 404);
        }

        // Return success response with the token
        return response()->json([
            'message' => 'Authenticated successfully.',
            'token' => $token,
            'server_info' => $metaServer,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Change the authenticated user's password.
     *
     * @param  \App\Http\Requests\ChangePasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user(); // Authenticated user

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'error' => 'The current password is incorrect.',
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->input('new_password'));
        $user->save();


        $user->tokens()->delete();


        return response()->json([
            'message' => 'Password changed successfully.',
        ], 200);
    }

    /**
     * Handle a failed login attempt.
     */
    protected function handleFailedLogin($user, Request $request)
    {
        $lockoutMinutes = 1;
        $maxAttempts = 3;
        $lockoutTime = now()->subMinutes($lockoutMinutes);
        $failedAttempts = StaffLoginAttempt::where('staff_id', $user->id)
            ->where('attempted_at', '>', $lockoutTime)
            ->count();

        if ($failedAttempts >= $maxAttempts) {
            $lastFailedAttempt = StaffLoginAttempt::where('staff_id', $user->id)
                ->where('attempted_at', '>', $lockoutTime)
                ->orderBy('id', 'desc')
                ->first();

            $timeDifference = now()->diffInSeconds($lastFailedAttempt->attempted_at);

            return response()->json([
                'error' => "Too many login attempts. Please try again in $timeDifference seconds.",
                'timerSec' => $timeDifference,
            ], 429); // 429 Too Many Requests
        }

        // Record the failed login attempt
        StaffLoginAttempt::create([
            'staff_id' => $user->id,
            'ip_address' => $request->ip(),
            'attempted_at' => now(),
        ]);

        return response()->json([
            'error' => 'Incorrect password.',
        ], 422);
    }

    /**
     * Check if the user is locked out due to too many failed attempts.
     */
    protected function isLockedOut($user)
    {
        $lockoutMinutes = 1;
        $maxAttempts = 3;
        $lockoutTime = now()->subMinutes($lockoutMinutes);
        $failedAttempts = StaffLoginAttempt::where('staff_id', $user->id)
            ->where('attempted_at', '>', $lockoutTime)
            ->count();

        return $failedAttempts >= $maxAttempts;
    }

    /**
     * Calculate the time difference for lockout.
     */
    protected function getLockoutTimeDifference($user)
    {
        $lockoutMinutes = 1;
        $lockoutTime = now()->subMinutes($lockoutMinutes);
        $lastFailedAttempt = StaffLoginAttempt::where('staff_id', $user->id)
            ->where('attempted_at', '>', $lockoutTime)
            ->orderBy('id', 'desc')
            ->first();

        return $lockoutTime->diffInSeconds($lastFailedAttempt->attempted_at);
    }

    /**
     * Logout method to revoke tokens.
     */
    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
