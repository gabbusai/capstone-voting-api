<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\VoteController;
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
    Route::get('/partylists/all', [CandidateController::class, 'getAllPartylist'])->name('api.getAllPartyList');

    //candidate pfp and posts
    Route::post('/candidates/{candidateId}/upload-photo', [CandidateController::class, 'uploadProfilePhoto']);
    Route::post('/candidates/posts/upload', [PostController::class, 'createPost']);
    Route::get('/candidates/posts/{id}', [PostController::class, 'getPost']);
    Route::put('/candidates/posts/update/{postId}', [PostController::class, 'updatePost']);
    Route::delete('/candidates/posts/delete/{postId}', [PostController::class, 'deletePost']);
    Route::get('/candidate-id/{student_id}', [CandidateController::class, 'getCandidateIdByStudentId']);
    //posts
    Route::get('/posts/approved', [PostController::class, 'getApprovedPosts']);
    Route::get('/posts/approved/{candidateId}', [PostController::class, 'getApprovedPostsByCandidate']);


    //votes
    Route::post('/vote/cast', [VoteController::class, 'castVote']);
    Route::get('/votes/election/{id}', [VoteController::class, 'getElectionDetails']);
    //results
    Route::get('/election/{id}/results', [VoteController::class, 'getElectionResults']);


});

//ADMIN ONLY ROUTES
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/election/registered', [ElectionController::class, 'getAllRegistered'])->name('api.getAllRegistered');
    Route::post('/file-candidate', [CandidateController::class, 'fileCandidacy'])->name('api.fileCandidacy'); // di to gagamitin
    Route::get('/candidates/position/{electionId}/{positionId}', [
        StudentController::class,
        'getCandidatesByPosition'
    ])->name('api.getCandidatesByPosition');

    //data needed by admin for their bs ahh dashboard
    Route::post('/admin/reset-password', [AdminController::class,'resetPassword']);
    Route::post('/admin/make-admin', [AdminController::class, 'makeAdmin']);
    Route::get('/admin/elections/all', [AdminController::class, 'adminGetElections']); // not working sadge pinagsamang elections with other useful data


    //make and edit elections
    Route::post('/elections/make', [AdminController::class, 'createElection'])->name('api.createElection'); //done
    

    //make and edit candidate
    Route::post('/verify-make-candidate', [AdminController::class, 'checkAndFileCandidacy'])->name('api.checkAndFileCandidacy'); //eto talaga dapat
    Route::put('/edit-candidate', [AdminController::class, 'updateCandidate']);
    

    //monitor and administrate posts
    Route::put('/admin/posts/{postId}/approve', [AdminController::class, 'approvePost'])->name('api.approvePost');
    Route::delete('/admin/remove-candidate/{userId}', [AdminController::class, 'removeCandidateStatus']);

    //make and edit position
    Route::post('/positions-make', [AdminController::class, 'makePosition']); //done 
    Route::put('/positions/{id}', [AdminController::class, 'updatePosition']); //done 
    Route::delete('/positions-delete/{id}', [AdminController::class, 'deletePosition']); //done 

    //make and edit partylist
    Route::post('/partylist-make', [AdminController::class, 'createPartylist']);

    //posts
    Route::get('/admin/posts/all', [AdminController::class, 'getAllPostsAdmin']);

    //department stuff
    Route::get('admin/departments/all', [AdminController::class, 'listDepartmentsAdmin']);
    Route::get('admin/departments/{id}', [AdminController::class, 'getDepartmentAdmin']);
    Route::post('admin/departments/', [AdminController::class, 'createDepartment']);
    Route::put('admin/departments/', [AdminController::class, 'updateDepartment']);
    Route::delete('admin/departments/{id}', [AdminController::class, 'deleteDepartment']);
    
});
//CANDIDATE ONLY ROUTES

//test routes
Route::post('/candidacy/test/{userId}/{positionId}', [
    StudentController::class,
    'testFileForCandidacy'
])->name('api.testFileForCandidacy');
Route::get('/candidate/all', [CandidateController::class, 'getAllCandidates'])->name('api.getCandidates');
Route::get('/positions/all', [CandidateController::class, 'getAllPositions'])->name('api.getPositions');
