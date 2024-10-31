<?php

namespace App\Http\Controllers;

use App\Http\Requests\MakeElectionRequest;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Position;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
class ElectionController extends Controller
{
    //show all elections
    public function getAllElections(){
    $elections = Election::all();
    if(is_null($elections)){
        return response()->json([
            'message' => 'No elections found'
        ]);
    }
        return response()->json($elections);
    }


    //show election details by ID
    public function getAnElection($electionId){
        $election = Election::find($electionId);
        if(is_null($election)){
            return response()->json([
                'message' => 'election not found'
            ]);
        }
        return response()->json($election);
    }

    public function createElection(MakeElectionRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();
    
        // Check if the user has admin role (role_id of 3)
        if ($user->role_id != 3) {
            return response()->json([
                'message' => 'You are not authorized to create an election.'
            ], 403);
        }
    
        // Create election with validated data
        $election = Election::create([
            'election_name' => $validatedData['election_name'],
            'election_type_id' => $validatedData['election_type_id'],
            'department_id' => $validatedData['department_id'], // Nullable for general elections
            'campaign_start_date' => $validatedData['campaign_start_date'],
            'campaign_end_date' => $validatedData['campaign_end_date'],
            'election_start_date' => $validatedData['election_start_date'],
            'election_end_date' => $validatedData['election_end_date'],
            'status' => $validatedData['status'] ?? 'upcoming', // Defaults to 'upcoming' if not provided
        ]);
    
        // Return a response with the created election details
        return response()->json([
            'message' => 'Election created successfully.',
            'election' => $election
        ], 201); // Status 201 for resource creation
    }
    

    public function getAllRegistered()
    {
        // Fetch all students who are associated with a user
        $students = Student::whereHas('user')->with('user')->get();
        $studentCount = Student::all()->count();
        $userCount = $students->count();
    
        return response()->json([
            'total_students' => $studentCount,
            'total_registered' => $userCount,
            'registered_students' => $students
        ], 200);
    }
    
}
