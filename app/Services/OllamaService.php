<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class OllamaService
{
    /**
     * Generate a response from the local Ollama LLM.
     */
    public function generate(string $model, string $prompt)
    {
        static $retry = 1;
        try{
            $url = config('app.llm.'.$model)['api'].'/generate';
            $model = config('app.llm.'.$model)['model'];

            $response = Http::post($url, [
                'model'  => 'gemma:2b',
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
            ]);

            if ($response->failed()) {
                throw new \Exception("Ollama request failed: " . $response->body());
            }

            $raw = $response->json()['response'] ?? '';
            \Log::info([$raw]);

            return json_decode($raw, true);
        }catch(\Exception $e){
            $retry++;
            if($retry > 3){return;}
            self::generate($model, $prompt);
        }
    }

    /**
    * Fix common JSON issues returned by LLMs.
    */
    private function cleanJson(string $text): string
    {
        // Remove trailing "▶" and extra characters
        $text = preg_replace('/▶.*/', '', $text);

        // Remove invalid trailing commas (very common in LLM output)
        $text = preg_replace('/,\s*}/', '}', $text);
        $text = preg_replace('/,\s*]/', ']', $text);

        // Trim whitespace
        return trim($text);
    }

    /**
     * Parse Claude's JSON response into structured array
     *
     * @param string $content
     * @return array
     */
    private function parseQuizResponse(string $content): array
    {
        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);
    
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from AI: ' . json_last_error_msg());
        }

        if (!isset($data['questions']) || !is_array($data['questions'])) {
            throw new \Exception('Invalid quiz format received from AI');
        }

        // Validate each question
        foreach ($data['questions'] as $question) {
            if (!isset($question['question']) || 
                !isset($question['options']) || 
                !isset($question['correct_answer'])) {
                throw new \Exception('Invalid question format in AI response');
            }

            if (count($question['options']) !== 4) {
                throw new \Exception('Each question must have exactly 4 options');
            }
        }

        return $data['questions'];
    }
}
