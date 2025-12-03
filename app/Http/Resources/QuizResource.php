<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'topic'         =>  $this->topic,
            'question'      =>  $this->question,
            'explanation'   =>  $this->explanation,
            'options'       =>  $this->options,
            'selected'      =>  $this->userSelectedAnswer?->answer,
            // 'userRank'      =>  $this->userRank,
        ];
    }
}
