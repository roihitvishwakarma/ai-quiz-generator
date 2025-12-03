<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Middleware\AuthCheck;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [AuthController::class, 'index'])->name('login');
Route::post('post-login', [AuthController::class, 'postLogin'])->name('login.post'); 
Route::get('registration', [AuthController::class, 'registration'])->name('register');
Route::post('post-registration', [AuthController::class, 'postRegistration'])->name('register.post'); 
Route::get('dashboard', [AuthController::class, 'dashboard']); 
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/generate', [QuizController::class, 'generateQuiz'])->name('quiz.generate')->middleware(AuthCheck::class);

Route::get('/generate-quiz', function(){
    return view('quiz.quiz-generator');
});

// Route::get('/quiz', function(){
//     return view('quiz.quiz');
// });

Route::get('/question', [QuizController::class, 'question'])->name('quiz.question')->middleware(AuthCheck::class);
Route::post('/submit-question', [QuizController::class, 'storeQuiz'])->name('quiz.submit.question')->middleware(AuthCheck::class);
Route::get('/quiz-history', [QuizController::class, 'quizHistory'])->name('quiz.history')->middleware(AuthCheck::class);