<?php
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

//PUBLIC ROUTES
Route::get('/getDepartments', [StudentController::class,
            'getAllDepartments'])->name('api.getDepartments');
Route::post('/candidacy/test/{userId}/{positionId}', [StudentController::class, 
            'testFileForCandidacy'])->name('api.testFileForCandidacy');

//PRIVATE ROUTES
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/send-verification', [VerificationController::class, 'sendVerification'])->name('api.sendVerification');
    Route::post('/verify-user', [VerificationController::class, 'verifyUser'])->name('api.verifyUser');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

//PRIVATE AND AUTHENTICATED USERS ONLY ROUTES
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/election/{electionId}', [ElectionController::class, 'getAnElection'])->name('api.getAnElection');
    Route::get('/elections', [ElectionController::class, 'getAllElections'])->name('api.getAllElections');
});

//ADMIN ONLY ROUTES
Route::middleware(['auth:sanctum', 'admin'])->group(function() {
    Route::get('/elections/registered', [ElectionController::class, 'getAllRegistered'])->name('api.getAllRegistered');
    Route::post('/file-candidate', [CandidateController::class, 'fileCandidacy'])->name('api.fileCandidacy');
    Route::get('/candidates/position/{electionId}/{positionId}', [StudentController::class, 
    'getCandidatesByPosition'])->name('api.getCandidatesByPosition');
    Route::post('/election/make', [ElectionController::class, 'createElection'])->name('api.makeElection');
});

//CANDIDATE ONLY ROUTES