<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\OllamaService;
use App\Repositories\QuestionRepository;

class QuizService
{
    private $client;
    private $apiUrl;
    private $llm;
    private $questionRepo;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('app.llm.gemma_7b');
        $this->llm = new OllamaService();
        $this->questionRepo = new QuestionRepository();
    }

    /**
     * Generate quiz questions
     *
     * @param string $topic
     * @param int $questionCount
     * @param bool $withExplanations
     * @return array
     */
    public function ask(string $topic, int $questionCount = 5, $model = 'gemma_7b'): array
    {
        $prompt = $this->draftPrompt($topic, $questionCount);

        try {

            $response = $this->llm->generate($model, $prompt);
            $response = isset($response['questions']) ? $response['questions'] : null;
            if(is_array($response)){
                return $this->questionRepo->store($response, $topic, auth()?->user()?->id ?? null);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Quiz generation failed: ' . $e->getMessage());
            throw new \Exception('Failed to generate quiz. Please try again.');
        }
    }

    /**
     * Build the prompt
     *
     * @param string $topic
     * @param int $questionCount
     * @param bool $withExplanations
     * @return string
     */
    private function draftPrompt(string $topic, int $questionCount): string
    {
        return <<<PROMPT
        You are an AI that outputs strictly valid JSON.

        Generate a multiple-choice quiz on the topic: "{$topic}"

        Requirements:
        - Exactly {$questionCount} questions.
        - Each question must have exactly 4 options: A, B, C, D.
        - Only ONE correct answer.
        - Provide a short 1–2 sentence explanation.
        - NO extra text, NO markdown, NO commentary.

        Return ONLY valid JSON in this exact structure:

        {
            "questions": [
                {
                "question": "string",
                "options": {
                    "A": "string",
                    "B": "string",
                    "C": "string",
                    "D": "string"
                },
                "correct_answer": "A",
                "explanation": "string"
                }
            ]
        }

        STRICT RULES:
        - Do NOT include any text outside the JSON.
        - Do NOT include trailing commas.
        - Do NOT format as code block.
        - Ensure JSON is syntactically valid.
        - Ensure the number of questions should be = {$questionCount}.

        PROMPT;
    }

    

    /**
     * Calculate quiz score
     *
     * @param array $questions
     * @param array $userAnswers
     * @return array
     */
    public function calculateScore(array $questions, array $userAnswers): array
    {
        $totalQuestions = count($questions);
        $correctAnswers = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            $questionNumber = $index + 1;
            $userAnswer = $userAnswers[$index] ?? null;
            $isCorrect = $userAnswer === $question['correct_answer'];

            if ($isCorrect) {
                $correctAnswers++;
            }

            $results[] = [
                'question_number' => $questionNumber,
                'question' => $question['question'],
                'user_answer' => $userAnswer,
                'correct_answer' => $question['correct_answer'],
                'is_correct' => $isCorrect,
                'options' => $question['options'],
                'explanation' => $question['explanation'] ?? null
            ];
        }

        return [
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percentage' => round(($correctAnswers / $totalQuestions) * 100, 2),
            'results' => $results
        ];
    }
}