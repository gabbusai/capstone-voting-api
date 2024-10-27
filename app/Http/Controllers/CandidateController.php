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
    
        // Fetch the user and position
        $user = User::find($validatedData['user_id']);
        $position = Position::find($validatedData['position_id']);
        $election = Election::find($validatedData['election_id']);
        // Check if user and position exist
        if (!$user || !$position || !$election) {
            return response()->json([
                'message' => 'User or Election or Position not found.'
            ], 404);
        }
    
        // Update the user's role to candidate (role_id 2)
        $user->role_id = 2;
        $user->save();
    
        // Create a new candidate record
        $candidate = Candidate::create([
            'student_id' => $user->student_id,
            'user_id' => $user->id,
            'election_id' => $validatedData['election_id'],
            'department_id' => $validatedData['department_id'],
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
    
}
