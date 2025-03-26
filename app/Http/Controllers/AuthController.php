<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Mail\SendOTP;
use App\Mail\WelcomeMail;
use App\Models\Student;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\TokenOTP;
class AuthController extends Controller
{

    use HttpResponses;
    /**
     * Display a listing of the resource.
     */

    public function logout(){
        Auth::user()->currentAccessToken()->delete();
        return $this->success([
            'message' => 'Successfully logged out'
        ], '');
    }

    //new login functions
    public function newLogin(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'email' => 'required|email',
            'device_id' => 'required'
        ]);
    
        // Check if student exists in the students table
        $student = Student::where('id', $request->student_id)->first();
        if (!$student) {
            return $this->error('', 'Student not found', 404);
        }
    
        // Check if the user already exists for this student
        $user = User::where('student_id', $student->id)->first();
        $isDeviceExisting = User::where('device_id', $request->device_id)->first();

        if($isDeviceExisting)
        {
            return $this->error('', 'device is already used by others', 403);
        }
        
        // If the user already exists, validate the device_id
        if ($user) {
            // Validate the device_id - Ensure the user is logging in from the correct device
            if ($user->device_id !== $request->device_id) {
                return $this->error('', 'Login not allowed on this device', 403);
            }

            if($user->role_id == 3){
                // Generate a random OTP token
                $otpToken = Str::random(6); // Generate a 6-character OTP
                $expiresAt = Carbon::now()->addDays(30); // Set expiration time to 30 minutes
            
                // Store the OTP token in the token_o_t_p_s table with an expiration time
                TokenOtp::create([
                    'user_id' => $user->id,
                    'tokenOTP' => $otpToken,
                    'expires_at' => $expiresAt, // OTP expires in 30 minutes
                    'used' => false, // OTP is not yet used
                ]);
            
                // Send the OTP token to the user's email via a Mailable class
                Mail::to($user->email)->send(new SendOTP($user, $otpToken));
            }
    
            return $this->success([
                'user' => $user,
                
            ], 'User already logged in from this device', 200);
        }
    
        // If the user doesn't exist, create a new one
        $user = User::create([
            'student_id' => $student->id,
            'department_id' => $student->department_id,
            'email' => $request->email,
            'name' => $student->name,
            'role_id' => 1, // Default role for students
            'device_id' => $request->device_id, // Store the device_id
        ]);
    
        // Generate a random OTP token
        $otpToken = Str::random(6); // Generate a 6-character OTP
        $expiresAt = Carbon::now()->addDays(30); // Set expiration time to 30 minutes
    
        // Store the OTP token in the token_o_t_p_s table with an expiration time
        TokenOtp::create([
            'user_id' => $user->id,
            'tokenOTP' => $otpToken,
            'expires_at' => $expiresAt, // OTP expires in 30 minutes
            'used' => false, // OTP is not yet used
        ]);
    
        // Send the OTP token to the user's email via a Mailable class
        Mail::to($user->email)->send(new SendOTP($user, $otpToken));
    
        // Return success response
        return $this->success([
            'user' => $user,
        ], 'OTP token sent to your email. Please check your inbox.');
    }
    
//verify otp
public function verifyOTP(Request $request)
{
    // Validate incoming request
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'tokenOTP' => 'required', // OTP Token
        'device_id' => 'required', // Device ID
    ]);

    // Fetch the student based on student_id (unencrypted)
    $student = Student::where('id', $request->student_id)->first();
    if (!$student) {
        return $this->error('', 'Student not found', 404);
    }

    // Fetch the user associated with the student (student_id is unencrypted)
    $user = User::where('student_id', $student->id)->first();
    if (!$user) {
        return $this->error('', 'User not found', 404);
    }

    // Verify device_id
    if ($user->device_id !== $request->device_id) {
        return $this->error('', 'Device ID does not match', 401);
    }

    // Fetch the latest OTP record for this user (unencrypted user_id)
    $tokenRecord = TokenOTP::where('user_id', $user->id)
                           ->where('used', false) // Ensure it’s not used
                           ->orderBy('created_at', 'desc') // Get the most recent
                           ->first();

    // Check if token exists and matches
    if (!$tokenRecord || $tokenRecord->tokenOTP !== $request->tokenOTP) {
        return $this->error('', 'Invalid OTP token', 404);
    }

    // Check if token is expired
    if (!$tokenRecord->expires_at || Carbon::now()->greaterThan($tokenRecord->expires_at)) {
        return $this->error('', 'OTP token has expired', 400);
    }

    // Mark the OTP as used to prevent reuse
    $tokenRecord->used = true;
    $tokenRecord->save();

    // Generate a new Bearer token for authentication
    $accessToken = $user->createToken('API Token of ' . $user->name)->plainTextToken;

    // Return the Bearer token in the response
    return $this->success([
        'access_token' => $accessToken,
        'token_type' => 'Bearer',
    ], 'OTP verified successfully.');
}

}


