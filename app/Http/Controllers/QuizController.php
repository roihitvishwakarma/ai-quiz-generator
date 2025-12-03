<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuizService;
use App\Repositories\QuestionRepository;

class QuizController extends Controller
{
    private QuizService $quizService;
    private QuestionRepository $questionRepo;
    
    public function __construct(QuizService $quizService, QuestionRepository $questionRepo){
        $this->quizService = $quizService;
        $this->questionRepo = $questionRepo;
    }

    public function generateQuiz(Request $request){

        $quiz = $this->quizService->ask($request->name, 5);

        if(empty($quiz)){
            return redirect()->route('generate-quiz');
            // return response()->json([
            //     "success" => false,
            //     "message" => "Something Went Wrong, Try Again Later.",
            // ], 500);
        }

        return redirect()->route('quiz.question');
    }

    public function question(){
        $questions = $this->questionRepo->getActiveQuestion();
        return view('quiz.quiz', compact('questions'));
    }

    public function storeQuiz(Request $request){
        $questions = $this->questionRepo->storeQizAnswer($request->answers);
        return redirect()->route('quiz.history');
    }

    public function quizHistory(){
        $attempts = $this->questionRepo->quizHistory();
        // return $attempts;
        return view('quiz.history', compact('attempts'));
    }
}
