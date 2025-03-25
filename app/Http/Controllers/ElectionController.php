<?php

namespace App\Http\Controllers;

use App\Http\Requests\MakeElectionRequest;
use App\Http\Requests\VoteRequest;
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
    public function getAllElections()
    {
        $elections = Election::all();
        $electionsCount = Election::all()->count();
        $activeElections = Election::whereIn('status', ['upcoming', 'ongoing'])
            ->get();
        $activeElectionsCount = Election::whereIn('status', ['upcoming', 'ongoing'])
            ->count();
        if (is_null($elections)) {
            return response()->json([
                'message' => 'No elections found'
            ]);
        }
        return response()->json([
            'elections' => $elections,
            'elections_count' => $electionsCount,
            'active_elections' => $activeElections,
            'active_elections_count' => $activeElectionsCount
        ], 200);
    }

    //show election details by ID
    public function getAnElection($electionId)
    {
        // Find the election by ID
        $election = Election::find($electionId);

        // If the election is not found, return a response with an error message
        if (is_null($election)) {
            return response()->json([
                'message' => 'Election not found nigga'
            ], 404);
        }

        // Fetch positions related to this election
        // Assuming 'department_id' is a foreign key in the 'positions' table
        $positions = Position::where('department_id', $election->department_id)->get();

        // Fetch candidates related to this election and eager load user, party_list, and department
        $candidates = Candidate::with(['user', 'partyList', 'department'])
            ->where('election_id', $electionId)
            ->get();

        // Return the election data with positions and candidates
        return response()->json([
            'election' => $election,
            'positions' => $positions,
            'candidates' => $candidates
        ]);
    }


    public function getAllRegistered()
    {
        // Fetch all students who are associated with a user
        $students = Student::whereHas('user')->with('user')->count();
        $studentCount = Student::all()->count();
        $userCount = $students;

        return response()->json([
            'total_students' => $studentCount,
            'total_registered' => $userCount,
            
        ], 200);
    }

    public function getRegisteredByDepartment()
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

    //send email to relevant voters


    //view all candidates of a specific election and specific group them by position
    public function getCandidatesByElection($electionId)
    {
        // Fetch the election and its candidates grouped by position
        $candidatesGrouped = Position::whereHas('candidates', function ($query) use ($electionId) {
            $query->where('election_id', $electionId);
        })
            ->with(['candidates' => function ($query) use ($electionId) {
                $query->where('election_id', $electionId);
            }])
            ->get()
            ->map(function ($position) {
                return [
                    'position' => $position->name,
                    'position_id' => $position->id,
                    'candidates' => $position->candidates->map(function ($candidate) {
                        return [
                            'id' => $candidate->id,
                            'name' => $candidate->user->name,
                            'student_id' => $candidate->student_id,
                            'department' => $candidate->department->name ?? 'N/A',
                            'party_list' => $candidate->partyList->name ?? 'Independent'
                        ];
                    })
                ];
            });

        // Check if there are candidates for the given election
        if ($candidatesGrouped->isEmpty()) {
            return response()->json([
                'message' => 'No candidates found for this election.'
            ], 404);
        }

        // Return the grouped data
        return response()->json([
            'message' => 'Candidates fetched successfully',
            'data' => $candidatesGrouped
        ], 200);
    }

    public function getPositionsForElection($electionId)
    {
        // Fetch the election by ID
        $election = Election::find($electionId);

        // Check if the election exists
        if (!$election) {
            return response()->json(['message' => 'Election not found.'], 404);
        }

        // Determine if the election is general or department-specific
        $isGeneralElection = is_null($election->department_id);

        // Fetch positions based on election type
        if ($isGeneralElection) {
            // For general elections, get all positions where is_general is true
            $positions = Position::where('is_general', true)->get();
        } else {
            // For department-specific elections, get positions related to the specific department
            $positions = Position::where('department_id', $election->department_id)
                ->get(); // Only department-specific positions
        }

        // Check if there are any positions available
        if ($positions->isEmpty()) {
            return response()->json(['message' => 'No positions available for this election.'], 404);
        }

        // Return the positions with their department details
        return response()->json([
            'message' => 'Positions fetched successfully',
            'election' => $election->election_name,
            'positions' => $positions->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'is_general' => $position->is_general,
                    'department' => $position->department ? $position->department->name : 'General'
                ];
            })
        ], 200);
    }

    //get voter relevant elections
    public function getUserElections()
    {
        // Fetch the authenticated user
        $user = User::find(Auth::user()->id);

        // Check if the user is part of a department
        $departmentId = $user->department_id;

        // Fetch elections where the user is related to:
        // 1. General elections (election_type_id = 1)
        // 2. Department-specific elections (where the department_id matches the user's department_id)
        $elections = Election::where(function ($query) use ($departmentId) {
            // General elections (election_type_id = 1)
            $query->where('election_type_id', 1)
                ->orWhere('department_id', $departmentId); // Department-specific elections
        })
            ->get(); // Fetch the results

        // Return the elections related to the user
        return response()->json([
            'message' => 'Elections fetched successfully.',
            'elections' => $elections
        ], 200);
    }

    

    
}
