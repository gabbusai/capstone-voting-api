<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VerificationController;
use App\Models\Candidate;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//AUTH PUBLIC ROUTES
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/admin-login', [AdminController::class, 'adminLogin'])->name('auth.adminLogin');
Route::post('/newLogin', [AuthController::class, 'newLogin'])->name('auth.NewLogin');
Route::post('/verifyOTP', [AuthController::class, 'verifyOTP'])->name('auth.NewLogin');

//PUBLIC ROUTES
Route::get('/getDepartments', [
    StudentController::class,
    'getAllDepartments'
])->name('api.getDepartments');
Route::get('/student/{student_id}', [StudentController::class, 'findStudentId'])->name('api.findStudentId');
Route::get('/student/exists/{student_id}', [StudentController::class, 'checkIfAccountExists'])->name('api.checkUserAccount');


Route::post('/student/validate-name', [StudentController::class, 'validateStudentName'])->name('api.validateName');
//PRIVATE ROUTES
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/send-verification', [VerificationController::class, 'sendVerification'])->name('api.sendVerification');
    Route::post('/verify-user', [VerificationController::class, 'verifyUser'])->name('api.verifyUser');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [StudentController::class, 'getUser'])->name('api.getUser');

    //test

    Route::get('/elections/relevant', [
        ElectionController::class,
        'getUserElections'
    ])->name('api.getCandidatesByPosition');
});

//PRIVATE AND AUTHENTICATED USERS ONLY ROUTES , 'verified'
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/candidates/{candidateId}', [CandidateController::class, 'getCandidate'])->name('api.getCandidate');
    Route::get('/elections/{electionId}/candidates', [ElectionController::class, 'getCandidatesByElection'])->name('api.viewCandidatesByElection');
    Route::get('/elections/{electionId}/positions', [ElectionController::class, 'getPositionsForElection'])->name('api.getPositionsOfElection');
    Route::get('/elections/{electionId}', [ElectionController::class, 'getAnElection'])->name('api.getAnElection');
    Route::get('/elections', [ElectionController::class, 'getAllElections'])->name('api.getAllElections');

    Route::post('/candidates/{candidateId}/upload-photo', [CandidateController::class, 'uploadProfilePhoto']);
    Route::post('/candidates/posts/upload', [PostController::class, 'createPost']);
    Route::get('/candidates/posts/{id}', [PostController::class, 'getPost']);
    Route::get('/candidates/posts/all', [PostController::class, 'getAllPosts']);
    Route::put('/candidates/posts/update/{postId}', [PostController::class, 'updatePost']);
    Route::delete('/candidates/posts/delete/{postId}', [PostController::class, 'deletePost']);
});

//ADMIN ONLY ROUTES
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/election/registered', [ElectionController::class, 'getAllRegistered'])->name('api.getAllRegistered');
    Route::post('/file-candidate', [CandidateController::class, 'fileCandidacy'])->name('api.fileCandidacy');
    Route::get('/candidates/position/{electionId}/{positionId}', [
        StudentController::class,
        'getCandidatesByPosition'
    ])->name('api.getCandidatesByPosition');
    Route::post('/elections/make', [AdminController::class, 'createElection'])->name('api.createElection');

    Route::post('/verify-make-candidate', [AdminController::class, 'checkAndFileCandidacy'])->name('api.checkAndFileCandidacy');
    Route::post('/posts/{postId}/approve', [AdminController::class, 'approvePost'])->name('api.approvePost');
    Route::delete('/admin/remove-candidate/{userId}', [AdminController::class, 'removeCandidateStatus']);

});
//CANDIDATE ONLY ROUTES

//test routes
Route::post('/candidacy/test/{userId}/{positionId}', [
    StudentController::class,
    'testFileForCandidacy'
])->name('api.testFileForCandidacy');
Route::get('/candidates/all', [CandidateController::class, 'getAllCandidates'])->name('api.getCandidates');
Route::get('/positions/all', [CandidateController::class, 'getAllPositions'])->name('api.getPositions');
