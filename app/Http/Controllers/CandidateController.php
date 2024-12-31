<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidacyFileRequest;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        // Check if the user is already a candidate in the same election
    $existingCandidate = Candidate::where('user_id', $user->id)
    ->where('election_id', $validatedData['election_id'])
    ->first();

    if ($existingCandidate) {
        return response()->json([
            'message' => 'User is already a candidate in this election.'
        ], 400);
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

    public function getCandidate($id){
        $candidate = Candidate::with(['user', 'election', 'department', 'partylist', 'position'])->find($id);
        return response()->json($candidate);
    }


// Upload candidate profile photo
public function uploadProfilePhoto(Request $request, $candidateId)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate([
        'profile_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $candidate = Candidate::findOrFail($candidateId);

    // Handle the file upload
    if ($request->hasFile('profile_photo')) {
        // Delete the existing profile photo if it exists
        if ($candidate->profile_photo) {
            $oldPath = public_path('storage/' . $candidate->profile_photo);
            if (file_exists($oldPath)) {
                unlink($oldPath); // Delete the old file from storage
            }
        }

        // Upload the new profile photo
        $file = $request->file('profile_photo');
        $path = $file->store('profile_photos', 'public'); // Save in storage/app/public/profile_photos

        // Update the candidate's profile photo path
        $candidate->profile_photo = $path;
        $candidate->save();

        return response()->json([
            'message' => 'Profile photo uploaded successfully.',
            'path' => $path,
        ], 200);
    }

    return response()->json(['message' => 'No file uploaded.'], 400);
}

    
}
