@extends(config('elseyyid-location.layout'))

@section(config('elseyyid-location.content_section'))
        @include('langs::includes.tools')
        @php $codes = explode(',', trim($settings->languages)); @endphp
        <h6 class="text-center font-weight-semibold fs-14 mt-5 mb-5 text-muted">{{__('Installed Languages')}}</h6>

        <div class="card card-body language-card mb-3 flex flex-row items-center justify-between">
            <h6 class="mb-0 fs-14 font-weight-semibold">{{ ucfirst('English') }}<span class="text-muted fs-10 ml-2">en</span></h6>
            <div>
                <label class="custom-switch language-switch-checkbox">
                    <input type="checkbox" name="language-checkbox" class="custom-switch-input" id="en" @if (in_array('en-US', $codes)) {{ 'checked' }} @endif @if (LaravelLocalization::getCurrentLocale() === 'en') {{ 'disabled' }} @endif>
                    <span class="custom-switch-indicator"></span>
                </label>
            </div>
        </div>

        @foreach ($langs as $lang)
            @php $lang_region = LaravelLocalization::getSupportedLocales()[str_replace('_','-',$lang)]['regional']; @endphp
            @php $lang_native = LaravelLocalization::getSupportedLocales()[str_replace('_','-',$lang)]['native']; @endphp
            <div class="card card-body language-card mb-3 flex flex-row items-center justify-between">
                <h6 class="mb-0 fs-14 font-weight-semibold">{{ ucfirst($lang_native) }}<span class="text-muted fs-10 ml-2">{{ $lang }}</span></h6>
                <div>
                    <label class="custom-switch language-switch-checkbox">
                        <input type="checkbox" name="language-checkbox" class="custom-switch-input" id="{{ $lang }}" @if (in_array($lang, $codes)) {{ 'checked' }} @endif @if (LaravelLocalization::getCurrentLocale() === $lang) {{ 'disabled' }} @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                    
                    <div class="btn-group dashboard-menu-button language-ellipsis">
                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis language-action-buttons"></i></button>
                        <div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
                            <a href="{{ route('elseyyid.translations.lang2', $lang) }}" class="dropdown-item"><i class="fa-solid fa-money-check-pen text-muted mr-2"></i> {{ __('Edit Strings') }}</a>	
                            <a href="{{ route('elseyyid.translations.lang.generateJson2', $lang) }}"  class="dropdown-item"><i class="fa-solid fa-file-lines text-muted mr-2"></i>{{ __('Generate JSON File') }}</a>	
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

@endsection
