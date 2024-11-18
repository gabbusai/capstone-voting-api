<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidacyFileRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\MakeElectionRequest;
use App\Http\Requests\StoreUserRequest;
use App\Mail\WelcomeMail;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionType;
use App\Models\PartyList;
use App\Models\Position;
use App\Models\Student;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    use HttpResponses;
    public function adminLogin(LoginUserRequest $request)
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


        // Check if the submitted student_id matches the user's student_id
        if ((string) $user->student_id !== (string) $request->student_id) {
            return $this->error('', 'Student ID does not match', 401);
        }

        // Check if the user is an admin (role_id = 3)
        if ($user->role_id !== 3) {
            return $this->error('', 'ACCESS DENIED: You are not an admin', 403);
        }

        // If credentials are correct and the user is an admin, authenticate the user
        Auth::login($user);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ], 'Login successful');
    }

    //make party list
    public function makePartyList(Request $request){
        $validatedData = $request->validated($request->all());
        //check if student number is in record db
        $partyList = PartyList::create([
            'name' => $request->name,
        ]);

        return $this->success([
            'party_list' => $partyList,
            'message' => "Party list successfully created"
        ], 'success');
    }

    //make candidate
    public function checkAndFileCandidacy(CandidacyFileRequest $request)
    {
        // Validate request data
        $validatedData = $request->validated();
    
        // Fetch the user by ID
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        // Check if the user is already a candidate
        $existingCandidate = Candidate::where('user_id', $user->id)->first();
        if ($existingCandidate) {
            return response()->json(['message' => 'You are already a registered candidate.'], 403);
        }
    
        // Fetch the position, election, and department
        $position = Position::find($validatedData['position_id']);
        $election = Election::find($validatedData['election_id']);
    
        // Check if the position and election exist
        if (!$position || !$election) {
            return response()->json(['message' => 'Position or Election not found.'], 404);
        }
    
        // Determine if the position is general or department-specific
        $isPositionGeneral = $position->is_general;
        $userDepartmentId = $user->department_id;
        $positionDepartmentId = $position->department_id;
    
        // Check eligibility based on the position type
        if (!$isPositionGeneral && $userDepartmentId !== $positionDepartmentId) {
            return response()->json([
                'message' => 'You are not eligible to run for this position. It is restricted to your department.'
            ], 403);
        }
    
        // Update the user's role to candidate (role_id 2)
        $user->role_id = 2;
        $user->save();
    
        // Create a new candidate record
        $candidate = Candidate::create([
            'student_id' => $user->student_id,
            'user_id' => $user->id,
            'election_id' => $validatedData['election_id'],
            'department_id' => $userDepartmentId,
            'position_id' => $validatedData['position_id'],
            'party_list_id' => $validatedData['party_list_id']
        ]);
    
        // Return success response
        return response()->json([
            'message' => 'Candidacy successfully filed.',
            'candidate' => $candidate,
            'user' => $user,
            'election' => $election
        ], 201);
    }
    
    public function createElection(MakeElectionRequest $request)
{
    // Fetch the authenticated user
    $user = User::find(Auth::user()->id);

    // Validate request data
    $validatedData = $request->validated();


    // Check if the user has admin role (role_id of 3)
    if ($user->role_id != 3) {
        return response()->json([
            'message' => 'You are not authorized to create an election.'
        ], 403);
    }

    // Ensure election_type_id is present
    if (!isset($validatedData['election_type_id'])) {
        return response()->json([
            'message' => 'Election type ID is required.'
        ], 400);
    }

    $electionTypeId = $validatedData['election_type_id'];
    //$electionType = ElectionType::find($electionTypeId);

    // Create the election record
    $election = new Election();
    $election->election_name = $validatedData['election_name'];
    $election->election_type_id = $electionTypeId;
    $election->department_id = $validatedData['department_id'] ?? null;
    $election->campaign_start_date = $validatedData['campaign_start_date'];
    $election->campaign_end_date = $validatedData['campaign_end_date'];
    $election->election_start_date = $validatedData['election_start_date'];
    $election->election_end_date = $validatedData['election_end_date'];
    $election->status = $validatedData['status'];
    $election->save();
    

    // Return a response with the created election details
    return response()->json([
        'message' => 'Election created successfully.',
        'election' => $election
    ], 201); // Status 201 for resource creation
}


    //make other users admin


    //monitor results


}
