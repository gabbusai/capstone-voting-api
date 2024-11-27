<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidacyFileRequest;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function fileCandidacy(CandidacyFileRequest $request)
    {
        // Validate request data
        $validatedData = $request->validated();
        $student_id = $validatedData['student_id'];
        
        // Fetch the user based on student_id, and ensure that it exists
        $user = User::where('student_id', $student_id)->first();
        
        // Fetch position and election
        $position = Position::find($validatedData['position_id']);
        $election = Election::find($validatedData['election_id']);
        
        // Check if user, position, or election exist
        if (!$user || !$position || !$election) {
            return response()->json([
                'message' => 'User or Election or Position not found.'
            ], 404);
        }
    
        // Ensure department_id of user(student) matches election's department_id
        if ($user->department_id !== $election->department_id) {
            return response()->json([
                'message' => 'User and Election department mismatch.'
            ], 400);
        }
    
        // Update the user's role to candidate (role_id 2)
        $user->role_id = 2;
        $user->save();
    
        // Get the user's department_id
        $userDep = $user->department_id;
    
        // Create a new candidate record
        $candidate = Candidate::create([
            'student_id' => $user->student_id,
            'user_id' => $user->id,
            'election_id' => $validatedData['election_id'],
            'department_id' => $userDep,
            'position_id' => $validatedData['position_id'],
            'party_list_id' => $validatedData['party_list_id']
        ]);
        
        // Return success response
        return response()->json([
            'message' => 'Candidacy successfully filed.',
            'candidate' => $candidate,
            'user' => $user->role_id,
            'election' => $election
        ], 201);
    }
    

    public function getAllCandidates()
    {
        $candidates = Candidate::with(['user', 'election'])->get();
        return response()->json($candidates);
    }

    public function getAllPositions(){
        $positions = Position::all();
        return response()->json($positions);
    }
    
}
