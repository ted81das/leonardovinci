<div class="row">

    <div class="col-sm-12">								
        <div class="card shadow-0 mb-5">							
            <div class="card-body">
                <div class="input-box mb-2">
                    <h6 class="text-muted fs-12">{{ __('Default Language') }} </h6>
                    <form action="{{route('elseyyid.translations.lang.setLocale2')}}" class="relative" method="GET">
                        <select id="setLocale" name="setLocale" class="form-select">
                            <option value="" disabled selected>{{__('Select Default Language')}}</option>
                            @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)                                
                                @if(in_array( $localeCode, explode(',', $settings->languages) ))
                                    <option value="{{$localeCode}}" @if( $settings->default_language === $localeCode) {{'selected'}} @endif>{{ucfirst($properties['native'])}} @if( $settings->default_language === $localeCode){{__('(Default Language)')}}@endif</option>
                                @endif
                            @endforeach																															
                        </select>
                        <button class="btn btn-primary locale-action-button" type="submit">{{ __('Set') }}</button>
                    </form>
                </div>	
            </div>
        </div>
    </div>

    <div class="col-sm-12">								
        <div class="card shadow-0">							
            <div class="card-body">
                <div class="input-box mb-2">
                    <h6 class="text-muted fs-12">{{ __('Add New Language') }} </h6>
                    <form action="{{route('elseyyid.translations.lang.newLang2')}}" class="relative" method="GET">
                        <select id="newLang" name="newLang" class="form-select">
                            <option value="" disabled selected>{{__('Add new language')}}</option>
                            @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                @if( !in_array($localeCode, languagesList()) )
                                    <option value="{{$localeCode}}"></span>{{ucfirst($properties['native'])}}</option>
                                @endif
                            @endforeach																															
                        </select>
                        <button class="btn btn-primary locale-action-button" type="submit">{{ __('Add') }}</button>
                    </form>
                </div>	
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 text-center">
            <a href="{{ route('elseyyid.translations.lang.publishAll2') }}" class="btn btn-primary pl-7 pr-7" style="text-transform: none">{{ __('Publish All JSON Files') }}</a>
        </div>
        <div class="col-sm-6 text-center">
            <a href="{{ route('elseyyid.translations.lang.reinstall') }}" class="btn btn-cancel pl-7 pr-7" style="text-transform: none">{{ __('Reinstall Language Files') }}</a>
        </div>
    </div>

</div>
