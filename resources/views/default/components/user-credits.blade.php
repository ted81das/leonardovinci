<div class="card-footer p-0">	
    <div class="row text-center pt-4 pb-4">
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->gpt_4_turbo_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableGPT4TWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('GPT 4 Turbo') }} {{ __('Words') }}</h6>
        </div>
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->gpt_4_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableGPT4Words() }} @endif</h4>
            <h6 class="fs-12">{{ __('GPT 4') }} {{ __('Words') }}</h6>
        </div>
    </div>							
    <div class="row text-center pt-4 pb-4">
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->gpt_4o_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableGPT4oWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('GPT 4o') }} {{ __('Words') }}</h6>
        </div>
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->gpt_3_turbo_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('GPT 3.5 Turbo') }} {{ __('Words') }}</h6>
        </div>
    </div>
    <div class="row text-center pt-4 pb-4">
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->claude_3_opus_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableClaudeOpusWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('Claude 3 Opus') }} {{ __('Words') }}</h6>
        </div>
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->claude_3_sonnet_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableClaudeSonnetWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('Claude 3.5 Sonnet') }} {{ __('Words') }}</h6>
        </div>
    </div>
    <div class="row text-center pt-4 pb-4">
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->claude_3_haiku_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableClaudeHaikuWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('Claude 3 Haiku') }} {{ __('Words') }}</h6>
        </div>
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->gemini_pro_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableGeminiProWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('Gemini Pro') }} {{ __('Words') }}</h6>
        </div>
    </div>
    <div class="row text-center pt-4 pb-4">
        <div class="col-sm">
            <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->fine_tune_credits == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableFineTuneWords() }} @endif</h4>
            <h6 class="fs-12">{{ __('Fine Tune') }} {{ __('Words') }}</h6>
        </div>
    </div>
    <div class="row text-center pt-4 pb-4">
        @role('user|subscriber|admin')
            @if (config('settings.image_feature_user') == 'allow')
                <div class="col-sm">
                    <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">{{ App\Services\HelperService::userAvailableDEImages() }}</h4>
                    <h6 class="fs-12">{{ __('Dalle Images Left') }}</h6>
                </div>
                <div class="col-sm">
                    <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">{{ App\Services\HelperService::userAvailableSDImages() }}</h4>
                    <h6 class="fs-12">{{ __('SD Images Left') }}</h6>
                </div>
            @endif
        @endrole
    </div>
    @if (config('settings.voiceover_feature_user') == 'allow' || config('settings.whisper_feature_user') == 'allow')
        <div class="row text-center pb-4">
            @role('user|subscriber|admin')
                @if (config('settings.voiceover_feature_user') == 'allow')
                    <div class="col-sm">
                        <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->available_chars == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableChars() }} @endif</h4>
                        <h6 class="fs-12">{{ __('Characters Left') }}</h6>
                    </div>
                @endif
            @endrole
            @role('user|subscriber|admin')
                @if (config('settings.whisper_feature_user') == 'allow')
                    <div class="col-sm">
                        <h4 class="mb-3 mt-1 font-weight-800 text-primary fs-16">@if (auth()->user()->available_minutes == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableMinutes() }} @endif</h4>
                        <h6 class="fs-12">{{ __('Minutes Left') }}</h6>
                    </div>
                @endif
            @endrole
        </div>
    @endif															
</div>