@extends('auth.layout')

@section('content')
<div class="container">

    <h2>Quiz</h2>

    <form action="{{ route('quiz.submit.question') }}" method="POST">
        @csrf
        @foreach($questions as $question)
            <div class="mb-4 p-3 border rounded">
                <h5>{{ $loop->iteration }}. {{ $question->question }}</h5>

                @foreach($question->options as $option)
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="answers[{{ $question->id }}]" 
                            value="{{ $option->id }}" 
                            id="option_{{ $option->id }}"
                            required
                        >
                        <label class="form-check-label" for="option_{{ $option->id }}">
                            {{ $option->answer }}  {{-- e.g., A, B, C, D --}}
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Submit Quiz</button>
    </form>

</div>
@endsection
