<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class LLMService
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
                'model'  => $model,
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
}
