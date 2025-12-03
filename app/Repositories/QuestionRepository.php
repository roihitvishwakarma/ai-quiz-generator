<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use App\Models\Option;
use Illuminate\Support\Str;

class QuestionRepository
{
    public Question $question;

    public function __construct(){
        $this->question = new Question();
    }

    public function store(array $questionsData, string $topic, int $userId): array 
    {
        if (empty($questionsData)) {
            return [];
        }

        DB::beginTransaction();
        try {
            $uuid = Str::uuid()->toString(); 
            foreach ($questionsData as $questionData) {
                $question = Question::create([
                    'topic'       => $topic,
                    'question'    => $questionData['question'],
                    'explanation' => $questionData['explanation'], 
                    'user_id'     => $userId,
                    'uuid'        => $uuid,
                ]);

                $optionsToInsert = [];
                $correctAnswerLetter = $questionData['correct_answer'];

                foreach ($questionData['options'] as $letter => $answerText) {
                    $isCorrect = ($letter === $correctAnswerLetter);

                    $optionsToInsert[] = new Option([
                        'option'       => $letter,
                        'answer'       => $answerText,
                        'is_correct'   => $isCorrect,
                    ]);
                }

                $question->options()->saveMany($optionsToInsert);
                $storedQuestions[] = $question;
            }

            DB::commit();

            return $storedQuestions;

        } catch (Exception $e) {
            DB::rollBack();
            dd($questionsData);
            \Log::error("Database transaction failed while storing quiz: " . $e->getMessage());
            throw new Exception("A database error occurred while saving the quiz.");
        }
    }

    public function getActiveQuestion(){
        $targetUuid = $this->question->where('active',1)
        ->whereHas('options')
        ->first();

        if (!$targetUuid) {
            return collect();
        }

        $questions = Question::query()
            ->where('active', 1)
            ->where('uuid', $targetUuid->uuid)
            ->whereHas('options')
            ->with('options:id,option,answer,question_id')
            ->get();

        return $questions;
    }

    public function storeQizAnswer(array $answers){
        $optionUpdate = false;
        $questionUpdate = false;

        foreach($answers as $questionId => $optionId){
            $optionUpdate = Option::find($optionId)->update(['user_answer' => 1]);
            $questionUpdate = $this->question::find($questionId)->update(['active' => 0]);
        }

        return $optionUpdate && $questionUpdate;
    }

    public function quizHistory(){
        $questionUuIds = Question::query()
        ->where('active', 0)
        ->whereHas('options')
        ->select('uuid', \DB::raw('MAX(id) as max_id'))
        ->orderBy('max_id', 'desc')
        ->groupBy('uuid')
        ->pluck('uuid')
        ->values()
        ->toArray();

        $quizes = [];

        foreach($questionUuIds as $questionUuId){
            $questions = Question::query()
            ->where('active', 0)
            ->where('uuid', $questionUuId)
            ->whereHas('options')
            ->with('options:id,option,answer,question_id','userSelectedAnswer')
            ->get();

             // STEP 2: Count user correct answers
            $correctCount = Option::whereIn('question_id', $questions->pluck('id'))
                ->where('user_answer', 1)
                ->where('is_correct', 1)
                ->count();

            // STEP 3: Push formatted quiz data
            $quizes[] = [
                'questions'     => $questions,
                'total_questions' => $questions->count(),
                'correct_answers' => $correctCount,
            ];
        }

        return $quizes;
    }
}
