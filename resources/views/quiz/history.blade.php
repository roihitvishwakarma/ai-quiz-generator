@extends('auth.layout')

@section('content')
<div class="container mx-auto p-4 sm:p-6 lg:p-8">

    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Past Quiz Attempts Review
    </h1>

    @if (empty($attempts) || count($attempts) === 0)
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 rounded-lg shadow-md">
            <p class="font-semibold">No Quiz Attempts Found</p>
            <p class="text-sm">You haven't completed any quizzes yet to review.</p>
        </div>
    @else

        @foreach($attempts as $index => $attempt)
            @php
                $questions = $attempt['questions'] ?? [];
                $totalQuestions = $attempt['total_questions'] ?? 0;
                $correctAnswers = $attempt['correct_answers'] ?? 0;

                $isPerfect = $correctAnswers === $totalQuestions;
                $submittedAt = isset($questions[0]['created_at'])
                                ? \Carbon\Carbon::parse($questions[0]['created_at'])->format('d M Y \a\t h:i A')
                                : '-';
            @endphp

            <div class="mb-4 bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
                {{-- Attempt Summary Header --}}
                <div class=" {{ $isPerfect ? 'bg-green-600' : 'bg-blue-600' }} flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-800">  Total Score : {{ $attempt['correct_answers'] }} / {{ $attempt['total_questions'] }} </h3>
                    <div>
                        <p class="text-sm opacity-90">Submitted: {{ $submittedAt }}</p>
                    </div>

                </div>

                {{-- Questions Breakdown --}}
                <div class="p-6">

                    @foreach ($questions as $qIndex => $q)

                        @php
                            $userAnswer = $q['userSelectedAnswer'] ?? null;
                            $isCorrect = $userAnswer && ($userAnswer['is_correct'] == 1);

                            $statusClass = $isCorrect ? 'bg-green-100 border-green-500' : 'bg-red-100 border-red-500';
                            $statusIcon  = $isCorrect ? '✔' : '❌';
                        @endphp

                        <div class="mb-6 p-4 rounded-lg border-l-4 {{ $statusClass }}">
                            {{-- Question --}}
                            <div class="flex items-start mb-2">
                                <span class="text-lg font-semibold mr-3">{{ $qIndex + 1 }}.</span>
                                <h3 class="text-lg font-medium text-gray-800">
                                    {{ $q['question'] }}
                                </h3>
                            </div>

                            {{-- User Answer --}}
                            <p class="font-semibold text-sm flex items-center">
                                <span class="mr-2">{{ $statusIcon }}</span>
                                Your Answer:
                                <span class="ml-2 font-bold p-1 rounded {{ $isCorrect ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $userAnswer['option'] ?? 'N/A' }}.
                                    {{ $userAnswer['answer'] ?? 'No Answer Selected' }}
                                </span>
                            </p>

                            {{-- Correct Answer (only show if wrong) --}}
                            @if (!$isCorrect)
                                @php
                                    $correct = collect($q['options'])->firstWhere('is_correct', 1);
                                @endphp

                                @if ($correct)
                                    <p class="mt-2 text-sm font-semibold text-gray-800">
                                        <span class="text-green-700 mr-2">✅</span>
                                        Correct Answer:
                                        <span class="ml-2 font-bold text-green-700">
                                            {{ $correct['option'] }}. {{ $correct['answer'] }}
                                        </span>
                                    </p>
                                @endif
                            @endif

                            {{-- Explanation --}}
                            @if (!empty($q['explanation']))
                                <div class="mt-3 pt-3 border-t border-gray-300">
                                    <p class="font-semibold text-sm text-gray-700">Explanation:</p>
                                    <p class="text-sm text-gray-600 italic mt-1">{{ $q['explanation'] }}</p>
                                </div>
                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach

    @endif

</div>
@endsection
