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

    //create election (unfinished)
    public function createElection(MakeElectionRequest $request){
        $user = Auth::user();
        $validatedData = $request->validated();
        if($user->role != 3){
            return response()->json([
                'message' => 'You are not authorized to create an election'
            ], 403);
        }
        //create election
        $election = Election::create([
            'election_name' => $validatedData['election_name'],
            'election_type_id' => $validatedData['election_type_id'],
            'department_id' => $validatedData['department_id'],
        ]);
        //send email to all Users
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
