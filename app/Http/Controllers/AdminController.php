<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidacyFileRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\MakeElectionRequest;
use App\Http\Requests\StoreUserRequest;
use App\Mail\WelcomeMail;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use Illuminate\Validation\Rule;
use App\Models\ElectionType;
use App\Models\PartyList;
use App\Models\Position;
use App\Models\Post;
use App\Models\Student;
use App\Models\TokenOTP;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteStatus;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use HttpResponses;

    public function adminLogin(Request $request)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'email' => 'required|email',
        'tokenOTP' => 'required'
    ]);

    // Fetch the student based on student_id (unencrypted)
    $student = Student::where('id', $request->student_id)->first();
    if (!$student) {
        return $this->error('', 'Student not found', 404);
    }

    // Find the user by student_id (unencrypted)
    $user = User::where('student_id', $request->student_id)->first();
    if (!$user) {
        return $this->error('', 'User not found', 404);
    }

    // Verify email (decrypted) matches the request
    if ($user->email !== $request->email) {
        return $this->error('', 'Email does not match', 401);
    }

    // Fetch the OTP record by user_id and verify tokenOTP (decrypted)
    $tokenRecord = TokenOTP::where('user_id', $user->id)->first();
    if (!$tokenRecord || $tokenRecord->tokenOTP !== $request->tokenOTP) {
        return $this->error('', 'Invalid OTP token', 404);
    }

    // Additional OTP checks
    if ($tokenRecord->user_id !== $user->id) {
        return $this->error('', 'Invalid OTP Token', 404);
    }

    // Check if the submitted student_id matches the user's student_id (redundant but kept for consistency)
    if ((string) $user->student_id !== (string) $request->student_id) {
        return $this->error('', 'Student ID does not match', 401);
    }

    // Check if the user is an admin (role_id = 3)
    if ($user->role_id !== 3) {
        return $this->error('', 'ACCESS DENIED: You are not an admin', 403);
    }

    // Authenticate the user
    Auth::login($user);

    return $this->success([
        'user' => $user,
        'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
    ], 'Login successful');
}

    //reset password
    public function resetPassword(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure the user is an admin
        if ($user->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized: Only admins can reset their password'], 403);
        }

        // Validate the request
        $request->validate([
            'new_password' => 'required|string|min:8', // No confirmed here since we'll handle it client-side
        ]);

        // Find the existing TokenOTP record for this user
        $tokenRecord = TokenOTP::where('user_id', $user->id)->first();

        if (!$tokenRecord) {
            // If no record exists, create one (shouldn't happen for admins, but added for robustness)
            $tokenRecord = new TokenOTP();
            $tokenRecord->user_id = $user->id;
        }

        // Update the tokenOTP with the new password
        $tokenRecord->tokenOTP = $request->new_password; // Store plain text as per your adminLogin
        $tokenRecord->save();

        return response()->json(['message' => 'Password successfully updated'], 200);
    }


    //make candidate
    public function checkAndFileCandidacy(CandidacyFileRequest $request)
    {
        // Validate request data
        $validatedData = $request->validated();

        // Fetch the user by student_id instead of user_id
        $user = User::where('student_id', $validatedData['student_id'])->first();
        if (!$user) {
            return response()->json(['message' => 'User not found with provided student ID.'], 404);
        }

        // Check if the user is already a candidate
        $existingCandidate = Candidate::where('user_id', $user->id)->first();
        if ($existingCandidate) {
            return response()->json(['message' => 'You are already a registered candidate.'], 403);
        }

        // Fetch the position and election
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

    //update candidate
    // In CandidateController.php
    public function updateCandidate(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'student_id' => 'required|exists:users,student_id',
            'position_id' => 'required|exists:positions,id',
            'election_id' => 'required|exists:elections,id',
            'party_list_id' => 'required|exists:party_lists,id',
        ]);

        // Fetch the user by student_id
        $user = User::where('student_id', $validated['student_id'])->first();
        if (!$user) {
            return response()->json(['message' => 'User not found with provided student ID.'], 404);
        }

        // Find the candidate associated with this user
        $candidate = Candidate::where('user_id', $user->id)->first();
        if (!$candidate) {
            return response()->json(['message' => 'No candidate found for this student ID.'], 404);
        }

        // Fetch the position and election
        $position = Position::find($validated['position_id']);
        $election = Election::find($validated['election_id']);

        if (!$position || !$election) {
            return response()->json(['message' => 'Position or Election not found.'], 404);
        }

        // Check position eligibility
        $isPositionGeneral = $position->is_general;
        $userDepartmentId = $user->department_id;
        $positionDepartmentId = $position->department_id;

        if (!$isPositionGeneral && $userDepartmentId !== $positionDepartmentId) {
            return response()->json([
                'message' => 'You are not eligible to run for this position. It is restricted to your department.'
            ], 403);
        }

        // Check if this update would create a duplicate candidacy (excluding current record)
        $existingCandidate = Candidate::where('user_id', $user->id)
            ->where('id', '!=', $candidate->id) // Exclude current candidate
            ->first();
        if ($existingCandidate) {
            return response()->json(['message' => 'This user is already a registered candidate for another position.'], 403);
        }

        // Update candidate record
        $candidate->update([
            'student_id' => $user->student_id,
            'user_id' => $user->id,
            'election_id' => $validated['election_id'],
            'department_id' => $userDepartmentId,
            'position_id' => $validated['position_id'],
            'party_list_id' => $validated['party_list_id']
        ]);

        // Ensure user's role remains candidate
        if ($user->role_id !== 2) {
            $user->role_id = 2;
            $user->save();
        }

        return response()->json([
            'message' => 'Candidate updated successfully!',
            'candidate' => $candidate->fresh(),
            'user' => $user,
            'election' => $election
        ], 200);
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

    public function editElection(Request $request)
    {
        // Check if the user is an admin
        $user = Auth::user();
        if ($user->role_id !== 3) { // 3 is the admin role_id
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        // Validate incoming request data
        $validated = $request->validate([
            'election_id' => 'required|integer|exists:elections,id',
            'election_name' => 'sometimes|string|max:255',
            'campaign_start_date' => 'sometimes|date|before:campaign_end_date',
            'campaign_end_date' => 'sometimes|date|after:campaign_start_date|before:election_start_date',
            'election_start_date' => 'sometimes|date|after:campaign_end_date|before:election_end_date',
            'election_end_date' => 'sometimes|date|after:election_start_date',
            'status' => 'sometimes|in:upcoming,ongoing,completed',
        ]);
    
        // Find the election
        $election = Election::find($validated['election_id']);
        if (!$election) {
            return response()->json(['message' => 'Election not found'], 404);
        }
    
        // Update only provided fields
        $election->update(array_filter([
            'election_name' => $request->election_name,
            'campaign_start_date' => $request->campaign_start_date,
            'campaign_end_date' => $request->campaign_end_date,
            'election_start_date' => $request->election_start_date,
            'election_end_date' => $request->election_end_date,
            'status' => $request->status,
        ]));
    
    
        // Return success response
        return response()->json([
            'message' => 'Election updated successfully',
            'election' => [
                'id' => $election->id,
                'election_name' => $election->election_name,
                'campaign_start_date' => $election->campaign_start_date,
                'campaign_end_date' => $election->campaign_end_date,
                'election_start_date' => $election->election_start_date,
                'election_end_date' => $election->election_end_date,
                'status' => $election->status,
            ]
        ], 200);
    }

    public function deleteElection(Request $request, $id)
    {
        // Check if the user is an admin
        $user = Auth::user();
        if ($user->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        // Find the election
        $election = Election::find($id);
        if (!$election) {
            return response()->json(['message' => 'Election not found'], 404);
        }
    
        try {
            
            // Delete the election
            $election->delete();
            return response()->json([
                'message' => 'Election deleted successfully',
                'election_id' => $id
            ], 200);
        } catch (\Exception $e) {
            
            return response()->json([
                'message' => 'Failed to delete election',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //make other users admin
    public function makeAdmin(Request $request)
    {
        // Ensure only authenticated admins can perform this action
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized: Only admins can promote users.'], 403);
        }

        // Validate the request
        $validated = $request->validate([
            'student_id' => 'required|exists:users,student_id',
        ]);

        // Find the user by student_id
        $user = User::where('student_id', $validated['student_id'])->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404); // Shouldn't happen due to exists rule, but added for safety
        }

        // Check if user is already an admin
        if ($user->role_id === 3) {
            return response()->json(['message' => 'User is already an admin.'], 400);
        }

        // Update the user's role to admin
        $user->role_id = 3;
        $user->save();

        return response()->json([
            'message' => 'User promoted to admin successfully.',
            'user' => [
                'id' => $user->id,
                'student_id' => $user->student_id,
                'role_id' => $user->role_id,
                'name' => $user->name, // Include if name exists in your User model
            ]
        ], 200);
    }

    //monitor results


    //approve post
    public function approvePost(Request $request, $postId)
    {
        $user = Auth::user();

        // Ensure the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if the user has admin privileges (assuming role_id 3 is for admin)
        if ($user->role_id !== 3) {
            return response()->json(['message' => 'Forbidden. Only admins can approve posts.'], 403);
        }

        // Find the post
        $post = Post::find($postId);

        // Check if the post exists
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        // Approve the post
        $post->is_approved = true;
        $post->save();

        return response()->json([
            'message' => 'Post approved successfully.',
            'post' => $post,
        ], 200);
    }

    public function removeCandidateStatus($userId)
    {
        // Check if the authenticated user is an admin
        $user = Auth::user();
        if ($user->role_id !== 3) { //3 is the admin role_id
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Check if the user is a candidate
        $candidate = Candidate::find($userId);
        $userToUpdate = $candidate->user;
        if (!$candidate) {
            return response()->json(['message' => 'User is not a candidate.'], 400);
        }

        // Remove the candidate's profile photo (if any)
        if ($candidate->profile_photo) {
            Storage::disk('public')->delete($candidate->profile_photo);
        }

        // Delete all posts associated with the candidate and their images
        foreach ($candidate->posts as $post) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->delete();
        }

        // Delete the candidate record
        $candidate->delete();

        // Revert the user's role to regular user (e.g., role_id 1)
        $userToUpdate->role_id = 1; // Assuming 1 is the role_id for a regular user
        $userToUpdate->save();

        return response()->json(['message' => 'Candidate status successfully removed and associated data deleted.'], 200);
    }





    //position related stuff
    public function makePosition(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name',
            'is_general' => 'required|boolean',
            'department_id' => [
                'integer', // Ensure it's an integer
                Rule::requiredIf(!$request->input('is_general')), // Required only if not general
                Rule::exists('departments', 'id'), // Ensure it exists in departments table
                Rule::when($request->input('is_general'), 'nullable'), // Nullable only if general
            ],
        ]);



        //position stuff
        $position = Position::create([
            'name' => $validated['name'],
            'is_general' => $validated['is_general'],
            'department_id' => $validated['is_general'] ? null : $validated['department_id'],
        ]);

        return response()->json([
            'message' => 'Position created successfully!',
            'position' => $position
        ], 201);
    }

    //update position
    public function updatePosition(Request $request, $id)
    {
        $position = Position::find($id);
        if (!$position) {
            return response()->json(['message' => 'Position not found.'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')->ignore($id), // Ignore current position for unique check
            ],
            'is_general' => 'required|boolean',
            'department_id' => [
                'integer',
                Rule::requiredIf(!$request->input('is_general')),
                Rule::exists('departments', 'id'),
                Rule::when($request->input('is_general'), 'nullable'),
            ],
        ]);

        $position->update([
            'name' => $validated['name'],
            'is_general' => $validated['is_general'],
            'department_id' => $validated['is_general'] ? null : $validated['department_id'],
        ]);

        return response()->json([
            'message' => 'Position updated successfully!',
            'position' => $position->fresh() // Get updated instance
        ], 200);
    }
    //delete position
    public function deletePosition($id)
    {
        $position = Position::find($id);
        if (!$position) {
            return response()->json(['message' => 'Position not found.'], 404);
        }

        // Check if the position is associated with any candidates
        $candidateCount = Candidate::where('position_id', $id)->count();
        if ($candidateCount > 0) {
            return response()->json([
                'message' => 'Cannot delete position. It is currently assigned to one or more candidates.'
            ], 403);
        }

        $position->delete();

        return response()->json([
            'message' => 'Position deleted successfully.'
        ], 200);
    }


    //MAKE PARTYLIST
    public function createPartylist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:party_lists,name', // Updated table name in unique rule
            'description' => 'nullable|string|max:500',
        ]);

        $partylist = Partylist::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Partylist created successfully!',
            'partylist' => $partylist
        ], 201);
    }

    public function deletePartylist($id)
    {
        $partylist = Partylist::find($id);
        if (!$partylist) {
            return response()->json(['message' => 'Party list not found.'], 404);
        }

        // Optional: Check if partylist has candidates
        if ($partylist->candidates()->count() > 0) {
            return response()->json(['message' => 'Cannot delete party list with associated candidates.'], 403);
        }

        $partylist->delete();
        return response()->json(['message' => 'Party list deleted successfully'], 200);
    }

    public function updatePartylist(Request $request, $id)
    {
        $partylist = PartyList::find($id);
        if (!$partylist) {
            return response()->json(['message' => 'partylist not found.'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('party_lists', 'name')->ignore($id), // Ignore current position for unique check
            ],
            'description' => 'required|string',
        ]);

        $partylist->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        return response()->json([
            'message' => 'Position updated successfully!',
            'partylist' => $partylist->fresh() // Get updated instance
        ], 200);
    }

    //data fetching needed for admin
    public function adminGetElections()
    {
        $elections = Election::with(['positions', 'department'])->get();
        $electionsCount = Election::all()->count();
        $activeElections = Election::whereIn('status', ['upcoming', 'ongoing'])
            ->with(['positions'])->get();
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

    // Fetch all posts with pagination
    public function getAllPostsAdmin(Request $request)
    {
        // Set default per page value, or use query parameter
        $perPage = $request->query('per_page', 10); // Default to 10 posts per page

        // Fetch paginated posts with relationships
        $posts = Post::with([
            'candidate' => function ($query) {
                $query->select('id', 'user_id', 'profile_photo', 'position_id', 'party_list_id')
                    ->with([
                        'user:id,name',
                        'position:id,name',
                        'partylist:id,name',
                        'department:id,name'
                    ]);
            }
        ])->paginate($perPage);

        return response()->json([
            'posts' => $posts->items(), // Current page items
            'pagination' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
            ],
        ], 200);
    }



    //department stuff
    public function listDepartmentsAdmin()
    {
        $departments = Department::withCount([
            'students', // Total students in the department
            'students as registered_count' => function ($query) {
                $query->whereHas('user'); // Students with a user instance
            }
        ])->get();

        $data = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'student_count' => $department->students_count,
                'registered_count' => $department->registered_count,
            ];
        });

        return response()->json([
            'message' => 'Departments retrieved successfully',
            'departments' => $data,
        ], 200);
    }

    /**
     * Get specific department by ID with student details
     */
    public function getDepartmentAdmin($id)
    {
        $department = Department::with(['students' => function ($query) {
            $query->select('id', 'name', 'department_id')
                  ->whereDoesntHave('user', function ($subQuery) {
                      $subQuery->where('role_id', 3); // Exclude students linked to admins
                  })
                  ->with(['user' => function ($query) {
                      $query->select('id', 'student_id');
                  }]);
        }])->find($id);
    
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }
    
        $students = $department->students->map(function ($student) {
            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'is_registered' => !is_null($student->user), 
            ];
        });
    
        return response()->json([
            'message' => 'Department retrieved successfully',
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'students' => $students,
            ],
        ], 200);
    }
    /**
     * Create a new department
     */
    public function createDepartment(Request $request)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        $department = Department::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Department created successfully',
            'department' => $department,
        ], 201);
    }

    //update department
    public function updateDepartment(Request $request, $id)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
        ]);

        $department->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department,
        ], 200);
    }

    //delete department
    public function deleteDepartment($id)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $department = Department::find($id);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        // Check if department has students (optional protection)
        if ($department->students()->count() > 0) {
            return response()->json(['message' => 'Cannot delete department with associated students'], 403);
        }

        $department->delete();

        return response()->json(['message' => 'Department deleted successfully'], 200);
    }


    public function listStudents(Request $request)
{
    $perPage = $request->query('per_page', 40);
    $search = $request->query('search');

    $query = Student::with(['user' => function ($query) {
        $query->select('id', 'student_id')->with(['tokenOTPs' => function ($query) {
            $query->select('id', 'user_id', 'tokenOTP', 'expires_at', 'used');
        }]);
    }]);

    // Only filter by unencrypted 'id' in the database if search is provided
    if ($search) {
        $query->where('id', 'like', "%{$search}%");
    }

    $students = $query->paginate($perPage);

    // Filter decrypted 'name' in memory if search is provided
    if ($search) {
        $students->setCollection(
            $students->getCollection()->filter(function ($student) use ($search) {
                return stripos($student->name, $search) !== false || // Decrypted name
                       stripos((string)$student->id, $search) !== false;
            })
        );
    }

    $data = $students->map(function ($student) {
        $user = $student->user;
        return [
            'id' => $student->id,
            'name' => $student->name, // Decrypted by cast
            'department_id' => $student->department_id,
            'is_registered' => !is_null($user),
            'tokenOTPs' => $user
                ? $user->tokenOTPs->map(function ($otp) {
                    return [
                        'id' => $otp->id,
                        'tokenOTP' => $otp->tokenOTP, // Decrypted by cast
                        'expires_at' => $otp->expires_at,
                        'used' => $otp->used,
                    ];
                })->toArray()
                : 'unregistered',
        ];
    });

    return response()->json([
        'message' => 'Students retrieved successfully',
        'students' => $data,
        'pagination' => [
            'total' => $students->total(),
            'per_page' => $students->perPage(),
            'current_page' => $students->currentPage(),
            'last_page' => $students->lastPage(),
            'from' => $students->firstItem(),
            'to' => $students->lastItem(),
            'next_page_url' => $students->nextPageUrl(),
            'prev_page_url' => $students->previousPageUrl(),
        ],
    ], 200);
}


    public function generateTokenOTP(Request $request)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $studentIds = $request->input('student_ids', []); // Array of student IDs
        $expiresAt = now()->addHours(24); // Default 24-hour expiration

        $results = [];
        foreach ($studentIds as $studentId) {
            $student = Student::with('user')->find($studentId);
            if (!$student) {
                $results[$studentId] = 'Student not found';
                continue;
            }
            if (!$student->user) {
                $results[$studentId] = 'Student not registered';
                continue;
            }

            $tokenOTP = TokenOTP::create([
                'user_id' => $student->user->id,
                'tokenOTP' => Str::random(6), // 6-character random token
                'expires_at' => $expiresAt,
                'used' => false,
            ]);

            $results[$studentId] = [
                'tokenOTP' => $tokenOTP->tokenOTP,
                'expires_at' => $tokenOTP->expires_at,
            ];
        }

        return response()->json([
            'message' => 'TokenOTPs generated successfully',
            'results' => $results,
        ], 200);
    }


    




/**
     * Create a new student
     */
    public function createStudent(Request $request)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $student = Student::create([
            'name' => $validated['name'],
            'department_id' => $validated['department_id'],
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student,
        ], 201);
    }

    /**
     * Modify an existing student
     */
    public function updateStudent(Request $request, $id)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'department_id' => 'sometimes|required|exists:departments,id',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student,
        ], 200);
    }

    /**
     * Delete a student
     */
    public function deleteStudent($id)
    {
        // Optional: Restrict to admins
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete(); // Cascades to user and tokenOTPs due to onDelete('cascade')

        return response()->json(['message' => 'Student deleted successfully'], 200);
    }

    //ELECTION and ELECTION RESULTS
    public function updateElectionStatus(Request $request, $electionId)
    {
        $election = Election::find($electionId);
        if (!$election) {
            return response()->json(['message' => 'Election not found'], 404);
        }

        // Validate the incoming status
        $request->validate([
            'status' => 'required|in:upcoming,ongoing,completed',
        ]);

        $newStatus = $request->input('status');
        $now = Carbon::now();

        // Prevent changing back to 'upcoming' from 'ongoing' or 'completed'
        if ($election->status !== 'upcoming' && $newStatus === 'upcoming') {
            return response()->json(['message' => 'Cannot revert election to upcoming status'], 400);
        }

        // Check if status change to 'ongoing' is allowed
        if ($newStatus === 'ongoing') {
            $startDate = Carbon::parse($election->election_start_date);
            if ($now->lt($startDate)) {
                return response()->json([
                    'message' => "Election cannot start yet. Start date is {$startDate->toDateTimeString()}",
                ], 400);
            }
        }

        // Check if status change to 'completed' is allowed
        if ($newStatus === 'completed') {
            $endDate = Carbon::parse($election->election_end_date);
            if ($now->lt($endDate)) {
                return response()->json([
                    'message' => "Election cannot be completed yet. End date is {$endDate->toDateTimeString()}",
                ], 400);
            }
            if ($election->status !== 'ongoing') {
                return response()->json(['message' => 'Election must be ongoing before marking as completed'], 400);
            }
        }

        // Update the status
        $election->status = $newStatus;
        $election->save();

        return response()->json([
            'message' => "Election status updated to '{$newStatus}' successfully",
            'election' => [
                'id' => $election->id,
                'name' => $election->election_name,
                'status' => $election->status,
                'election_start_date' => $election->election_start_date,
                'election_end_date' => $election->election_end_date,
            ],
        ], 200);
    }

        // Delete a post
        public function adminDeletePost($id)
        {
            // Get the authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Ensure the user is an admin
            if ($user->role_id !== 3) {
                return response()->json(['message' => 'Unauthorized: Only admins can reset their password'], 403);
            }

            $post = Post::find($id);
    
            if (!$post) {
                return response()->json(['message' => 'Post not found.'], 404);
            }
    
            // Delete the image file if it exists
            if ($post->image) {
                $path = public_path('storage/' . $post->image);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
    
            $post->delete();
    
            return response()->json([
                'message' => 'Post deleted successfully.',
            ], 200);
        }


    //get admin election results
    public function getAdminElectionResults($electionId)
    {
        // Fetch the election with candidates and relationships
        $election = Election::with(['candidates.position', 'candidates.partylist', 'candidates.user'])
            ->find($electionId);
    
        if (!$election) {
            return response()->json(['message' => 'Election not found'], 404);
        }
    
        // Total registered students (potential voters), excluding admins
        $totalVoters = Student::where(function ($query) use ($election) {
            if ($election->department_id) {
                $query->where('department_id', $election->department_id);
            }
        })->whereDoesntHave('user', function ($query) {
            $query->where('role_id', 3); // Exclude admins
        })->count();
    
        // Total votes cast (unique voters), excluding admins
        $votesCast = Vote::where('election_id', $electionId)
            ->whereHas('voter', function ($query) {
                $query->whereDoesntHave('user', function ($subQuery) {
                    $subQuery->where('role_id', 3); // Exclude admins
                });
            })
            ->distinct('voter_student_id')
            ->count('voter_student_id');
    
        // Calculate turnout percentage
        $turnoutPercentage = $totalVoters > 0 ? round(($votesCast / $totalVoters) * 100, 2) : 0;
    
        // Fetch vote tallies per candidate, including position_id, excluding admin votes
        $tallies = Vote::where('election_id', $electionId)
            ->whereHas('voter', function ($query) {
                $query->whereDoesntHave('user', function ($subQuery) {
                    $subQuery->where('role_id', 3); // Exclude admins
                });
            })
            ->join('candidates', 'votes.candidate_id', '=', 'candidates.id')
            ->selectRaw('candidates.position_id, candidates.id as candidate_id, COUNT(*) as vote_count')
            ->groupBy('candidates.position_id', 'candidates.id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->candidate_id => ['vote_count' => $item->vote_count, 'position_id' => $item->position_id]];
            });
    
        // Count unique voters per position to calculate abstains
        $votersPerPosition = Vote::where('election_id', $electionId)
            ->whereHas('voter', function ($query) {
                $query->whereDoesntHave('user', function ($subQuery) {
                    $subQuery->where('role_id', 3); // Exclude admins
                });
            })
            ->join('candidates', 'votes.candidate_id', '=', 'candidates.id')
            ->selectRaw('candidates.position_id, COUNT(DISTINCT votes.voter_student_id) as voter_count')
            ->groupBy('candidates.position_id')
            ->pluck('voter_count', 'position_id');
    
        // Organize results by position
        $results = [];
        foreach ($election->candidates as $candidate) {
            $positionId = $candidate->position->id;
            $positionName = $candidate->position->name;
    
            if (!isset($results[$positionId])) {
                $votesForPosition = $votersPerPosition[$positionId] ?? 0;
                $abstains = $totalVoters - $votesForPosition;
    
                $results[$positionId] = [
                    'position_id' => $positionId,
                    'position_name' => $positionName,
                    'total_eligible_voters' => $totalVoters,
                    'votes_cast' => $votesForPosition,
                    'abstains' => $abstains,
                    'candidates' => [],
                    'winners' => [],
                ];
            }
    
            $voteCount = $tallies[$candidate->id]['vote_count'] ?? 0;
    
            $results[$positionId]['candidates'][] = [
                'candidate_id' => $candidate->id,
                'student_id' => $candidate->student_id,
                'name' => $candidate->user->name ?? 'Unknown',
                'profile_photo' => $candidate->profile_photo ?? null,
                'partylist' => $candidate->partylist->name ?? 'Independent',
                'votes' => $voteCount,
            ];
        }
    
        // Handle positions with no candidates and determine winners
        foreach ($results as &$position) {
            if (empty($position['candidates'])) {
                $position['winners'] = ['No candidates for this position'];
                $position['votes_cast'] = 0;
                $position['abstains'] = $totalVoters; // All eligible voters abstained
                continue;
            }
    
            $position['candidates'] = collect($position['candidates'])->sortByDesc('votes')->values();
    
            if ($position['candidates'][0]['votes'] === 0) {
                $position['winners'] = ['No votes received for this position'];
            } else {
                $highestVote = $position['candidates'][0]['votes'];
                $position['winners'] = $position['candidates']
                    ->filter(fn($candidate) => $candidate['votes'] === $highestVote)
                    ->values();
            }
        }
    
        return response()->json([
            'election' => [
                'id' => $election->id,
                'name' => $election->election_name,
                'status' => $election->status,
                'department_id' => $election->department_id,
                'total_voters' => $totalVoters,
                'votes_cast' => $votesCast,
                'turnout_percentage' => $turnoutPercentage,
            ],
            'results' => array_values($results),
        ], 200);
    }
    
//unused
    public function getElectionTurnout(Request $request, $electionId)
{
    $election = Election::find($electionId);
    if (!$election) {
        return response()->json(['message' => 'Election not found'], 404);
    }

    // Total registered students (potential voters)
    $totalVoters = Student::where(function ($query) use ($election) {
        if ($election->department_id) {
            $query->where('department_id', $election->department_id);
        }
    })->count();

    // Total votes cast (unique voters)
    $votesCast = Vote::where('election_id', $electionId)
        ->distinct('voter_student_id')
        ->count('voter_student_id');

    // Turnout percentage
    $turnoutPercentage = $totalVoters > 0 ? round(($votesCast / $totalVoters) * 100, 2) : 0;

    // Paginated voters list with search
    $perPage = $request->query('per_page', 10);
    $search = $request->query('search');

    $votersQuery = Vote::where('election_id', $electionId)
        ->join('students', 'votes.voter_student_id', '=', 'students.id')
        ->join('users', 'votes.user_id', '=', 'users.id')
        ->join('candidates', 'votes.candidate_id', '=', 'candidates.id')
        ->select(
            'votes.voter_student_id as student_id',
            'users.name as voter_name',
            'students.department_id',
            'votes.created_at as vote_date',
            'candidates.id as candidate_id',
            'candidates.user_id as candidate_user_id'
        )
        ->with([
            'candidate.user' => fn($q) => $q->select('id', 'name'),
            'election' => fn($q) => $q->select('id', 'election_name')
        ]);

    if ($search) {
        $votersQuery->where(function ($query) use ($search) {
            $query->where('users.name', 'like', "%{$search}%")
                  ->orWhere('votes.voter_student_id', 'like', "%{$search}%");
        });
    }

    $voters = $votersQuery->paginate($perPage);

    // Format voter data
    $voterList = $voters->map(function ($vote) {
        return [
            'student_id' => $vote->student_id,
            'name' => $vote->voter_name,
            'department_id' => $vote->department_id,
            'vote_date' => $vote->vote_date,
            'voted_for' => [
                'candidate_id' => $vote->candidate_id,
                'candidate_name' => $vote->candidate->user->name ?? 'Unknown',
            ],
        ];
    });

    return response()->json([
        'election' => [
            'id' => $election->id,
            'name' => $election->election_name,
            'status' => $election->status,
            'total_voters' => $totalVoters,
            'votes_cast' => $votesCast,
            'turnout_percentage' => $turnoutPercentage,
        ],
        'voters' => $voterList,
        'pagination' => [
            'total' => $voters->total(),
            'per_page' => $voters->perPage(),
            'current_page' => $voters->currentPage(),
            'last_page' => $voters->lastPage(),
            'from' => $voters->firstItem(),
            'to' => $voters->lastItem(),
            'next_page_url' => $voters->nextPageUrl(),
            'prev_page_url' => $voters->previousPageUrl(),
        ],
    ], 200);
}


//election turnouts
public function getAdminElectionTurnout(Request $request, $electionId)
{
    $election = Election::find($electionId);
    if (!$election) {
        return response()->json(['message' => 'Election not found'], 404);
    }

    // Total registered students (potential voters), excluding admins
    $totalVoters = Student::where(function ($query) use ($election) {
        if ($election->department_id) {
            $query->where('department_id', $election->department_id);
        }
    })->whereDoesntHave('user', function ($query) {
        $query->where('role_id', 3); // Exclude admins
    })->count();

    // Total votes cast (unique voters), excluding admins
    $votesCast = Vote::where('election_id', $electionId)
        ->whereHas('user', function ($query) {
            $query->where('role_id', '!=', 3); // Exclude admins
        })
        ->distinct('voter_student_id')
        ->count('voter_student_id');

    // Turnout percentage
    $turnoutPercentage = $totalVoters > 0 ? round(($votesCast / $totalVoters) * 100, 2) : 0;

    // Paginated voters list with search, excluding admins
    $perPage = $request->query('per_page', 10);
    $search = $request->query('search');

    $votersQuery = Vote::where('election_id', $electionId)
        ->whereHas('user', function ($query) {
            $query->where('role_id', '!=', 3); // Exclude admins
        })
        ->with([
            'voter' => fn($q) => $q->select('id', 'department_id'),
            'user' => fn($q) => $q->select('id', 'name', 'student_id'),
            'candidate.user' => fn($q) => $q->select('id', 'name'),
            'election' => fn($q) => $q->select('id', 'election_name')
        ]);

    if ($search) {
        $votersQuery->where(function ($query) use ($search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhere('voter_student_id', 'like', "%{$search}%");
        });
    }

    $voters = $votersQuery->paginate($perPage);

    // Format voter data
    $voterList = $voters->map(function ($vote) {
        return [
            'student_id' => $vote->voter_student_id,
            'name' => $vote->user->name ?? 'Unknown',
            'department_id' => $vote->voter->department_id,
            'vote_date' => $vote->created_at,
            'voted_for' => [
                'candidate_id' => $vote->candidate_id,
                'candidate_name' => $vote->candidate->user->name ?? 'Unknown',
            ],
        ];
    });

    return response()->json([
        'election' => [
            'id' => $election->id,
            'name' => $election->election_name,
            'status' => $election->status,
            'total_voters' => $totalVoters,
            'votes_cast' => $votesCast,
            'turnout_percentage' => $turnoutPercentage,
        ],
        'voters' => $voterList,
        'pagination' => [
            'total' => $voters->total(),
            'per_page' => $voters->perPage(),
            'current_page' => $voters->currentPage(),
            'last_page' => $voters->lastPage(),
            'from' => $voters->firstItem(),
            'to' => $voters->lastItem(),
            'next_page_url' => $voters->nextPageUrl(),
            'prev_page_url' => $voters->previousPageUrl(),
        ],
    ], 200);
}

    //get student count per department
    public function getStudentCountByDep()
{
    try {
        // Total students across all departments, excluding admins
        $totalStudents = Student::whereDoesntHave('user', function ($query) {
            $query->where('role_id', 3); // Exclude students linked to admins
        })->count();

        // Stats per department: total students and registered students, excluding admins
        $departmentStats = Department::select('departments.id', 'departments.name')
            ->leftJoin('students', 'departments.id', '=', 'students.department_id')
            ->leftJoin('users', 'students.id', '=', 'users.student_id')
            ->where(function ($query) {
                $query->whereNull('users.role_id') // Students without users
                      ->orWhere('users.role_id', '!=', 3); // Non-admin users
            })
            ->groupBy('departments.id', 'departments.name')
            ->selectRaw('COUNT(DISTINCT students.id) as total_students')
            ->selectRaw('COUNT(DISTINCT users.id) as registered_students')
            ->get()
            ->map(function ($department) {
                return [
                    'department_id' => $department->id,
                    'department_name' => $department->name,
                    'total_students' => (int) $department->total_students,
                    'registered_students' => (int) $department->registered_students,
                ];
            });

        // Total registered students across all departments, excluding admins
        $totalRegistered = Student::whereHas('user', function ($query) {
            $query->where('role_id', '!=', 3); // Exclude admins
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => $totalStudents,
                'total_registered' => $totalRegistered,
                'departments' => $departmentStats,
            ],
            'message' => 'Student statistics retrieved successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve student statistics.',
            'error' => $e->getMessage(),
        ], 500);
    }
}




    public function getElectionStatistics($electionId)
{
    $election = Election::with('electionType')->findOrFail($electionId);

    // Determine eligible voters based on election type, excluding admins
    $eligibleVotersQuery = Student::whereDoesntHave('user', function ($query) {
        $query->where('role_id', 3); // Exclude admins
    });

    if ($election->election_type_id == 2) { // Departmental election
        $eligibleVotersQuery->where('department_id', $election->department_id);
    }

    // Count total eligible voters
    $totalEligibleVoters = $eligibleVotersQuery->count();

    // Get eligible voters per department, excluding admins
    $departmentEligibleVoters = Student::whereDoesntHave('user', function ($query) {
        $query->where('role_id', 3); // Exclude admins
    })
    ->when($election->election_type_id == 2, function ($query) use ($election) {
        return $query->where('department_id', $election->department_id);
    })
    ->groupBy('department_id')
    ->select('department_id', DB::raw('COUNT(*) as eligible_count'))
    ->pluck('eligible_count', 'department_id');

    // Get voter turnout by department, excluding admins
    $departmentTurnout = DB::table('vote_statuses')
        ->join('users', 'vote_statuses.user_id', '=', 'users.id')
        ->join('students', 'users.student_id', '=', 'students.id')
        ->join('departments', 'students.department_id', '=', 'departments.id')
        ->where('vote_statuses.election_id', $electionId)
        ->where('users.role_id', '!=', 3) // Exclude admins
        ->select(
            'departments.id as department_id',
            'departments.name as department_name',
            DB::raw('COUNT(DISTINCT users.id) as total_voters'),
            DB::raw('COUNT(DISTINCT CASE WHEN vote_statuses.has_voted = true THEN users.id END) as voted_count')
        )
        ->groupBy('departments.id', 'departments.name')
        ->get();

    // Calculate total voted
    $totalVoted = $departmentTurnout->sum('voted_count');

    // Map department turnout with additional calculations
    $departmentTurnout = $departmentTurnout->map(function ($dept) use ($totalEligibleVoters, $departmentEligibleVoters, $totalVoted) {
        $departmentEligibleCount = $departmentEligibleVoters[$dept->department_id] ?? 0;
        return [
            'department_id' => $dept->department_id,
            'department_name' => $dept->department_name,
            'eligible_voters' => $departmentEligibleCount,
            'voted_count' => $dept->voted_count,
            'overall_turnout_percentage' => $totalEligibleVoters > 0 
                ? round(($dept->voted_count / $totalEligibleVoters) * 100, 2) 
                : 0,
            'department_turnout_percentage' => $departmentEligibleCount > 0 
                ? round(($dept->voted_count / $departmentEligibleCount) * 100, 2) 
                : 0,
            'votes_distribution_percentage' => $totalVoted > 0
                ? round(($dept->voted_count / $totalVoted) * 100, 2)
                : 0
        ];
    });

    $overallTurnoutPercentage = $totalEligibleVoters > 0 
        ? round(($totalVoted / $totalEligibleVoters) * 100, 2) 
        : 0;

    return response()->json([
        'election_details' => $election,
        'total_eligible_voters' => $totalEligibleVoters,
        'total_voted' => $totalVoted,
        'overall_turnout_percentage' => $overallTurnoutPercentage,
        'department_turnout' => $departmentTurnout
    ]);
}
    //voter election status
    public function electionVoterStatus(Request $request, $electionId)
    {
        // Check if user is admin
        $user = Auth::user();
        if ($user->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        // Find the election
        $election = Election::findOrFail($electionId);
    
        $perPage = $request->query('per_page', 15);
        $isGeneralElection = $election->election_type_id === 1;
    
        // Base query for eligible students, excluding admins
        $eligibleStudentsQuery = Student::query()
            ->with('user')
            ->whereDoesntHave('user', function ($query) {
                $query->where('role_id', 3); // Exclude admins
            })
            ->when(!$isGeneralElection, function ($query) use ($election) {
                return $query->where('department_id', $election->department_id);
            });
    
        // Get users who voted in this election, excluding admins
        $votedUserIds = VoteStatus::where('election_id', $electionId)
            ->whereHas('user', function ($query) {
                $query->where('role_id', '!=', 3); // Exclude admins
            })
            ->pluck('user_id')
            ->toArray();
    
        // Apply search filter
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $eligibleStudentsQuery->where(function ($query) use ($searchTerm) {
                $query->where('id', 'like', "%{$searchTerm}%")
                      ->orWhere('name', 'like', "%{$searchTerm}%")
                      ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'like', "%{$searchTerm}%");
                      });
            });
        }
    
        // Apply voting status filter
        $votingStatus = $request->query('voting_status');
        if ($votingStatus !== null) {
            $eligibleStudentsQuery->whereHas('user', function ($query) use ($votedUserIds, $votingStatus) {
                $query->when($votingStatus === 'voted', function ($subQuery) use ($votedUserIds) {
                    return $subQuery->whereIn('id', $votedUserIds);
                })->when($votingStatus === 'not_voted', function ($subQuery) use ($votedUserIds) {
                    return $subQuery->whereNotIn('id', $votedUserIds);
                });
            });
        }
    
        // Apply department filter
        $departmentId = $request->query('department_id');
        if ($departmentId) {
            $eligibleStudentsQuery->where('department_id', $departmentId);
        }
    
        // Apply sorting
        $sortBy = $request->query('sort_by', 'id');
        $sortDirection = $request->query('sort_direction', 'asc');
        $allowedSortColumns = ['id', 'name', 'department_id'];
        $allowedSortDirections = ['asc', 'desc'];
    
        if (in_array($sortBy, $allowedSortColumns) && in_array($sortDirection, $allowedSortDirections)) {
            $eligibleStudentsQuery->orderBy($sortBy, $sortDirection);
        }
    
        // Paginate students
        $students = $eligibleStudentsQuery->paginate($perPage);
    
        // Transform the data
        $voterStatus = $students->map(function ($student) use ($votedUserIds) {
            $user = $student->user;
            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'No User Account',
                'department_id' => $student->department_id,
                'has_voted' => $user && in_array($user->id, $votedUserIds),
            ];
        });
    
        return response()->json([
            'election' => [
                'id' => $election->id,
                'name' => $election->election_name,
                'type' => $election->electionType->name ?? 'Unknown',
            ],
            'voters' => $voterStatus,
            'pagination' => [
                'current_page' => $students->currentPage(),
                'total_pages' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'next_page_url' => $students->nextPageUrl(),
                'prev_page_url' => $students->previousPageUrl(),
            ],
        ], 200);
    }



}
