<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Position;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{   

    public function getUser()
{
    $user = User::with(['department', 'role'])->find(Auth::user()->id);

    // Ensure user exists
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json($user);
}

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

    public function findStudentId($student_id)
    {
        // Validate that the student_id is provided
        if (!$student_id) {
            return response()->json([
                'success' => false,
                'message' => 'Student ID is required.'
            ], 400); // Bad Request
        }
    
        // Search for the student ID in the database
        $student = Student::where('id', $student_id)->first();
    
        // Check if the student was found
        if ($student) {
            return response()->json([
                'success' => true,
                'message' => 'Student ID exists.',
                'data' => $student // Optional: Include student details
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Student ID not found.'
            ], 404); // Not Found
        }
    }

    public function checkIfAccountExists($student_id)
    {
        // Validate that the student_id is provided
        if (!$student_id) {
            return response()->json([
                'success' => false,
                'message' => 'Student ID is required.'
            ], 400); // Bad Request
        }
    
        // Check if a student exists with the provided student_id
        $student = Student::where('id', $student_id)->first();
    
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'No student found with the provided ID.'
            ], 404); // Not Found
        }
    
        // Check if a user account exists for this student
        $user = User::where('student_id', $student->id)->first();
    
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Account exists.',
                'data' => [
                    'user' => $user, // Include user details if needed
                ],
            ], 200); // OK
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No account exists for this student.',
            ], 404); // Not Found
        }
    }
    
    public function validateStudentName(Request $request)
    {
    // Validate the request
    $validatedData = $request->validate([
        'student_id' => 'required|string|max:255', // Ensure student_id is provided
        'name' => 'required|string|max:255', // Ensure name is provided
    ]);

    // Extract validated data
    $student_id = $validatedData['student_id'];
    $name = $validatedData['name'];

    // Search for the student by ID
    $student = Student::where('id', $student_id)->first();

    // Check if the student exists
    if (!$student) {
        return response()->json([
            'success' => false,
            'message' => 'Student ID not found.'
        ], 404); // Not Found
    }

    // Validate if the name matches
    if (strtolower($student->name) === strtolower($name)) {
        return response()->json([
            'success' => true,
            'message' => 'Student ID and name match.',
            'data' => $student // Optional: include student details
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'The name does not match the student ID.'
        ], 422); // Unprocessable Entity
    }
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
