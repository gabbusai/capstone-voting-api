<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function getAllDepartments(){
        $departments = Department::all();
        return response()->json([
            'departments' => $departments
        ]);
    }

    public function getCandidatesByPosition($electionId, $positionId)
    {
        // Fetch candidates filtered by position_id and eager load the related user and department
        $position = Position::find($positionId);
        $election = Election::find($electionId);
        if(is_null($election)){
            return response()->json([
                'message'=>'Election Not Found'
            ], 404); 
        }

        $candidates = Candidate::with(['user', 'department', 'partyList', 'election']) // Eager load the related models
        ->where('position_id', $positionId)
        ->where('election_id', $electionId) // Add filter for election_id
        ->get();

        //if not general and election department doesnt match position department
        if($election->election_type_id == 2 && $election->department_id != $position->department_id){
            return response()->json([
                'message' => 'Position is not in the Election',
                'election' => $election->election_name,
                'position' => $position->name,
            ], 403);
        }else if($election->election_type_id == 1 && is_null($election->department_id) && $position->department_id )
        { //if general and election department is null
            return response()->json([
                'message' => 'Position is not in the Election',
                'election' => $election->election_name,
                'position' => $position->name,
            ], 403);
        }
        // Check if any candidates were found
        if ($candidates->isEmpty()) {
            return response()->json([
                'election' => $election->election_name,
                'position' => $position,
                'message' => 'No candidates found for this position.'
            ], 404);
        }
        // Transform the candidates data
        $formattedCandidates = $candidates->map(function ($candidate) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->user->name, // Fetch name from User model
                'department' => $candidate->department->name, // Fetch name from Department model
                'position' => $candidate->position->name, // Adjust this if you want to fetch the actual position name
                'party_list' => $candidate->partyList->name, // Fetch name from PartyList model
            ];
        });
    
        // Return the candidates as a JSON response
        return response()->json([
            'election' => $election->election_name,
            'position for' => $position,
            'candidates' => $formattedCandidates,
            'election_type_id' => $election->electionType->id,
            'election_department_id' => $election->department_id,
            'position_department_id' => $position->department_id
        ], 200);
    }


    public function testFileForCandidacy($userId, $positionId)
    {
        // Fetch the candidate associated with the user
        $candidate = Candidate::where('user_id', $userId)->first();
    
        // Check if the user is a candidate
        if (!$candidate) {
            return response()->json([
                'message' => 'You are not a registered candidate.'
            ], 403); // Forbidden
        } 
    
        // Fetch the position
        $position = Position::find($positionId);
    
        // Check if the position exists
        if (!$position) {
            return response()->json([
                'message' => 'Position does not exist.'
            ], 404); // Not found
        }
    
        // Fetch the user's department
        $userDepartmentId = $candidate->department_id;
        $isPositionGeneral = $position->is_general;
        $positionDepartmentId = $position->department_id;
    
        // Check eligibility based on position type
        if ($isPositionGeneral) {
            // For general positions, all candidates are eligible
            return response()->json([
                'message' => 'You are eligible to run for this general position.'
            ], 200);
        } else {
            // For department-specific positions, check if the user's department matches the position's department
            if ($userDepartmentId === $positionDepartmentId) {
                return response()->json([
                    'message' => 'You are eligible to run for this department-specific position.'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'You are not eligible to run for this position. It is restricted to your department.'
                ], 403);
            }
        }
    }
    

    
    
}
