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
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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


        // Fetch the student based on student_id
        $student = Student::where('id', $request->student_id)->first();

        if (!$student) {
            return $this->error('', 'Student not found', 404);
        }

        // Find the user by student_id or email
        $user = User::where('student_id', $request->student_id)
            ->orWhere('email', $request->email)
            ->first();
        // Ensure the user exists
        if (!$user) {
            return $this->error('', 'User not found', 404);
        }

        /*if($user->device_id !== $request->device_id){
            return $this->error('', 'Device ID does not match', 401);
        } */

        if ($user->email != $request->email) {
            return $this->error('', 'Email does not match', 401);
        }

        // Fetch the OTP record using the token provided
        $tokenRecord = TokenOTP::where('tokenOTP', $request->tokenOTP)->first();

        // Check if token is invalid or expired
        if (!$tokenRecord) {
            return $this->error('', 'Invalid OTP token', 404);
        }

        if ($tokenRecord->user_id !== $user->id) {
            return $this->error('', 'Invalid OTP Token', 404);
        }

        if ($tokenRecord->tokenOTP != $request->tokenOTP) {
            return $this->error('', 'Invalid OTP token', 404);
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
        $perPage = $request->query('per_page', 2); // Default to 10 posts per page

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

    























}
