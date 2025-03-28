<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Election;
use App\Models\Feedback;
use App\Models\Position;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function getAllDepartments()
    {
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
        if (is_null($election)) {
            return response()->json([
                'message' => 'Election Not Found'
            ], 404);
        }

        $candidates = Candidate::with(['user', 'department', 'partyList', 'election']) // Eager load the related models
            ->where('position_id', $positionId)
            ->where('election_id', $electionId) // Add filter for election_id
            ->get();

        //if not general and election department doesnt match position department
        if ($election->election_type_id == 2 && $election->department_id != $position->department_id) {
            return response()->json([
                'message' => 'Position is not in the Election',
                'election' => $election->election_name,
                'position' => $position->name,
            ], 403);
        } else if ($election->election_type_id == 1 && is_null($election->department_id) && $position->department_id) { //if general and election department is null
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


    //puno na admin controller so dito na man since student stuff naman hehe
    public function listStudents(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');

        $query = Student::with(['user', 'tokenOTPs' => function ($query) {
            $query->select('id', 'student_id', 'tokenOTP', 'expires_at', 'used');
        }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate($perPage);

        $data = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'department_id' => $student->department_id,
                'is_registered' => !is_null($student->user),
                'tokenOTPs' => $student->user
                    ? $student->tokenOTPs->map(fn($otp) => [
                        'id' => $otp->id,
                        'tokenOTP' => $otp->tokenOTP,
                        'expires_at' => $otp->expires_at,
                        'used' => $otp->used,
                    ])
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

    public function sendFeedback(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'stars' => 'required|integer|between:1,5',
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|max:5000',
            ]);

            // Ensure the user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Unauthorized: You must be logged in to submit feedback.',
                    'success' => false
                ], 401);
            }


            // Create the feedback
            $feedback = Feedback::create([
                'user_id' => Auth::user()->id,
                'stars' => $validated['stars'],
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]);

            return response()->json([
                'message' => 'Feedback submitted successfully.',
                'success' => true,
                'data' => [
                    'id' => $feedback->id,
                    'stars' => $feedback->stars,
                    'title' => $feedback->title,
                    'content' => $feedback->content,
                    'created_at' => $feedback->created_at->toIso8601String(),
                    'user' => [
                        'id' => $feedback->user->id,
                        'name' => $feedback->user->name,
                    ]
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while submitting feedback.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function importStudents(Request $request)
    {
        try {
            // Validate the uploaded file
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the uploaded file
            $file = $request->file('file');
            $path = $file->getRealPath();

            // Read file content and remove BOM if present
            $content = file_get_contents($path);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Remove UTF-8 BOM
            $tempPath = tempnam(sys_get_temp_dir(), 'csv');
            file_put_contents($tempPath, $content);

            // Open and read the CSV
            if (($handle = fopen($tempPath, 'r')) === false) {
                unlink($tempPath);
                return response()->json([
                    'message' => 'Failed to open the CSV file.',
                    'success' => false
                ], 500);
            }

            $students = [];
            $errors = [];
            $rowNumber = 0;
            $validDepartments = Department::pluck('id')->toArray(); // Cache valid department IDs

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;

                // Validate row structure
                if (count($data) !== 3) {
                    $errors[] = "Row $rowNumber: Invalid format. Expected 3 columns (student_id, name, department_id), got " . count($data);
                    continue;
                }

                [$studentId, $name, $departmentId] = $data;

                // Clean and validate data
                $studentId = trim($studentId);
                $name = trim($name);
                $departmentId = trim($departmentId);

                if (!is_numeric($studentId) || !is_numeric($departmentId) || empty($name)) {
                    $errors[] = "Row $rowNumber: Invalid data - student_id ($studentId) and department_id ($departmentId) must be numeric, name cannot be empty.";
                    continue;
                }

                $studentId = (int) $studentId;
                $departmentId = (int) $departmentId;

                // Check if department_id exists
                if (!in_array($departmentId, $validDepartments)) {
                    $errors[] = "Row $rowNumber: Department ID $departmentId does not exist.";
                    continue;
                }

                $students[] = [
                    'id' => $studentId,
                    'name' => Crypt::encryptString($name),
                    'department_id' => $departmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            fclose($handle);
            unlink($tempPath);

            if (empty($students) && !empty($errors)) {
                return response()->json([
                    'message' => 'No valid data found in the CSV.',
                    'success' => false,
                    'errors' => $errors
                ], 422);
            }

            // Use upsert within a transaction
            DB::transaction(function () use ($students) {
                Student::upsert(
                    $students,
                    ['id'],
                    ['name', 'department_id', 'updated_at']
                );
            });

            return response()->json([
                'message' => 'Students imported successfully.',
                'success' => true,
                'count' => count($students),
                'errors' => $errors ?: null // Include errors if any rows were skipped
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while importing students.',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function paginatedFeedbacks(request $request)
    {
        // Set default per page value, or use query parameter
        $perPage = $request->query('per_page', 2);
        $search = $request->query('search');
        // Fetch paginated feedbacks with relationships
        $query = Feedback::with([
            'user'
        ]);

        if($search){
            $query->whereAny([
                'stars',
                'title',
                'content',
            ], 'like', "%$search%" );
        }
        $feedbacks = $query->paginate($perPage);

        return response()->json([
            'feedbacks' => $feedbacks->items(), // Current page items
            'pagination' => [
                'total' => $feedbacks->total(),
                'per_page' => $feedbacks->perPage(),
                'current_page' => $feedbacks->currentPage(),
                'last_page' => $feedbacks->lastPage(),
                'from' => $feedbacks->firstItem(),
                'to' => $feedbacks->lastItem(),
                'feedbacks' => $feedbacks->nextPageUrl(),
                'prev_page_url' => $feedbacks->previousPageUrl(),
            ],
        ], 200);
    }
}
