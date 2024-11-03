<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Mail\WelcomeMail;
use App\Models\Student;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    use HttpResponses;
    /**
     * Display a listing of the resource.
     */

    //login mobile app
    public function login(LoginUserRequest $request)
    {   
        $request->validated($request->all());
        // Find the user by student_id or email
        $user = User::where('student_id', $request->student_id)
                    ->orWhere('email', $request->email)
                    ->first();
        // Check if the user exists and the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('', 'CREDENTIALS DO NOT MATCH', 401);
        }
        // If credentials are correct, authenticate the user
        Auth::login($user);
        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ], 'Login successful');
    }

    //register mobile app
    public function register(StoreUserRequest $request){
    $validatedData = $request->validated($request->all());
    //check if student number is in record db
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'student_id' => $request->student_id,
        'department_id' => $request->department_id,
        'role_id' => $request->role_id,
        'contact_no' => $request->contact_no,
        'section' => $request->section
    ]);
    

    //mail to user providing the token
    Mail::to($user->email)->send(New WelcomeMail($user));
    
    //event(new Registered($user));
    return $this->success([
        'user' => $user,
        'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken
    ], 'success');
}


    public function logout(){
        Auth::user()->currentAccessToken()->delete();
        return $this->success([
            'message' => 'Successfully logged out'
        ], '');
    }

    //verification stuff
}
