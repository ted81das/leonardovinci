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
        @else
            @foreach ($fine_tunes as $fine_tune)
                @if (trim($model) == $fine_tune->model)
                    <option value="{{ trim($model) }}">{{ $fine_tune->description }} ({{ __('Fine Tune') }})</option>
                @endif
            @endforeach
        @endif
        
    @endforeach									
</select>	