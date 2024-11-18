<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VerificationController;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//AUTH PUBLIC ROUTES
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/admin-login', [AdminController::class, 'adminLogin'])->name('auth.adminLogin');

//PUBLIC ROUTES
Route::get('/getDepartments', [StudentController::class,
            'getAllDepartments'])->name('api.getDepartments');

//PRIVATE ROUTES
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/send-verification', [VerificationController::class, 'sendVerification'])->name('api.sendVerification');
    Route::post('/verify-user', [VerificationController::class, 'verifyUser'])->name('api.verifyUser');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [StudentController::class, 'getUser'])->name('api.getUser');

    //test
                
Route::get('/elections/relevant', [ElectionController::class, 
'getUserElections'])->name('api.getCandidatesByPosition');
});

//PRIVATE AND AUTHENTICATED USERS ONLY ROUTES
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/elections/{electionId}/candidates', [ElectionController::class, 'getCandidatesByElection'])->name('api.viewCandidatesByElection');
    Route::get('/elections/{electionId}/positions', [ElectionController::class, 'getPositionsForElection'])->name('api.getPositionsOfElection');
    Route::get('/elections/{electionId}', [ElectionController::class, 'getAnElection'])->name('api.getAnElection');
    Route::get('/elections', [ElectionController::class, 'getAllElections'])->name('api.getAllElections');
});

//ADMIN ONLY ROUTES
Route::middleware(['auth:sanctum', 'admin'])->group(function() {
    Route::get('/election/registered', [ElectionController::class, 'getAllRegistered'])->name('api.getAllRegistered');
    Route::post('/file-candidate', [CandidateController::class, 'fileCandidacy'])->name('api.fileCandidacy');
    Route::get('/candidates/position/{electionId}/{positionId}', [StudentController::class, 
    'getCandidatesByPosition'])->name('api.getCandidatesByPosition');
    Route::post('/elections/make', [AdminController::class, 'createElection'])->name('api.createElection');
    Route::post('/verify-make-candidate', [AdminController::class, 'checkAndFileCandidacy'])->name('api.checkAndFileCandidacy');
});
//CANDIDATE ONLY ROUTES

//test routes
Route::post('/candidacy/test/{userId}/{positionId}', [StudentController::class, 
            'testFileForCandidacy'])->name('api.testFileForCandidacy');

            
