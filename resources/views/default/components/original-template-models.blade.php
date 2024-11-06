<select id="model" name="model" class="form-select" onchange="updateModel()">   	
    @foreach ($models as $model)
        @if (trim($model) == 'gpt-3.5-turbo-0125')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | GPT 3.5 Turbo') }}</option>
        @elseif (trim($model) == 'gpt-4')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | GPT 4') }}</option>
        @elseif (trim($model) == 'gpt-4o')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | GPT 4o') }}</option>
        @elseif (trim($model) == 'gpt-4o-mini')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | GPT 4o mini') }}</option>
        @elseif (trim($model) == 'gpt-4-0125-preview')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | GPT 4 Turbo') }}</option>
        @elseif (trim($model) == 'gpt-4-turbo-2024-04-09')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | GPT 4 Turbo with Vision') }}</option>
        @elseif (trim($model) == 'o1-preview')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | o1 preview') }}</option>
        @elseif (trim($model) == 'o1-mini')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | o1 mini') }}</option>
        @elseif (trim($model) == 'claude-3-opus-20240229')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('Anthropic | Claude 3 Opus') }}</option>
        @elseif (trim($model) == 'claude-3-5-sonnet-20240620')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('Anthropic | Claude 3.5 Sonnet') }}</option>
        @elseif (trim($model) == 'claude-3-haiku-20240307')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('Anthropic | Claude 3 Haiku') }}</option>
        @elseif (trim($model) == 'gemini_pro')
            <option value="{{ trim($model) }}" @if (trim($model) == $default_model) selected @endif>{{ __('Google | Gemini Pro') }}</option>
        @else
            @foreach ($fine_tunes as $fine_tune)
                @if (trim($model) == $fine_tune->model)
                    <option value="{{ $fine_tune->model }}" @if (trim($model) == $default_model) selected @endif>{{ __('OpenAI | ') }} {{ $fine_tune->description }}</option>
                @endif
            @endforeach
        @endif
        
    @endforeach									
</select>