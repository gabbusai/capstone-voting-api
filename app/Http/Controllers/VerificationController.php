<?php

namespace App\Http\Controllers;

use App\Mail\VerificationMail;
use App\Models\User;
use App\Models\VerificationCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Mail\WelcomeMail;

class VerificationController extends Controller
{
    public function sendVerification()
    {
        // Get the authenticated user
        $user = Auth::user();
        // Generate a unique 6-digit code
        $code = mt_rand(100000, 999999);

        // Define expiration date (e.g., 15 minutes from now)
        $expirationDate = Carbon::now()->addMinutes(15);

        // Save the code in the verification_codes table
        VerificationCodes::create([
            'user_id' => $user->id,
            'code' => $code,
            'expiration_date' => $expirationDate,
            'isExpired' => false
        ]);

        // Send the code to the user's email
        Mail::to($user->email)->send(new VerificationMail($user, $code));

        return response()->json(['message' => 'Verification code sent to your email.'], 200);
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6', 
        ]);
        $user = User::find(Auth::user()->id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        $code = $request->input('code');
    
        // Find the most recent verification code for the user that is not expired
        $verificationCode = VerificationCodes::where('user_id', $user->id)
            ->where('code', $code)
            ->where('isExpired', false)
            ->where('expiration_date', '>=', now())
            ->first();
    
        if (!$verificationCode) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 403);
        }
    
        // Mark the code as expired
        $verificationCode->update(['isExpired' => true]);
    
        // Update the user's email verification timestamp
        $user->email_verified_at = now();
        $user->save();
        
        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
