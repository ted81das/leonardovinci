<div class="row no-gutters">
    <nav class="navbar navbar-expand-lg navbar-light w-100" id="navbar-responsive">
        <a class="navbar-brand" href="{{ url('/') }}"><img id="brand-img"  src="{{ URL::asset($settings->logo_frontend) }}" alt="Davinci AI logo"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse section-links" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link scroll active" data="#main" href="{{ url('/') }}">{{ __('Home') }} <span class="sr-only">(current)</span></a>
                </li>	
                @if ($frontend_sections->features_status)
                    <li class="nav-item">
                        <a class="nav-link scroll" data="#features" href="{{ url('/') }}/#features">{{ __('Features') }}</a>
                    </li>
                @endif	
                @if ($frontend_sections->pricing_status)
                    <li class="nav-item">
                        <a class="nav-link scroll" data="#prices" href="{{ url('/') }}/#prices">{{ __('Pricing') }}</a>
                    </li>
                @endif							
                @if ($frontend_sections->faq_status)
                    <li class="nav-item">
                        <a class="nav-link scroll" data="#faqs" href="{{ url('/') }}/#faqs">{{ __('FAQs') }}</a>
                    </li>
                @endif	
                @if ($frontend_sections->blogs_status)
                    <li class="nav-item">
                        <a class="nav-link scroll" data="#blogs" href="{{ url('/') }}/#blogs">{{ __('Blogs') }}</a>
                    </li>
                @endif	
                @if ($custom_pages)
                    @foreach ($custom_pages as $page)
                        @if ($page->show_main_nav)
                            <li class="nav-item">
                                <a class="nav-link scroll" href="{{ url('/') }}/page/{{ $page->slug }}">{{ __($page->title) }}</a>
                            </li>
                        @endif  
                    @endforeach
                @endif									
            </ul>    
            @if (Route::has('login'))
            <div id="login-buttons" class="pr-4">
                <div class="dropdown header-languages" id="frontend-local">
                    <a class="icon" data-bs-toggle="dropdown">
                        <span class="header-icon fa-solid fa-globe mr-4 fs-15"></span>
                    </a>
                    <div class="dropdown-menu animated">
                        <div class="local-menu">
                            @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                @if (in_array($localeCode, explode(',', $settings->languages)))
                                    <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" class="dropdown-item d-flex pl-4" hreflang="{{ $localeCode }}">
                                        <div>
                                            <span class="font-weight-normal fs-12">{{ ucfirst($properties['native']) }}</span> <span class="fs-10 text-muted">{{ $localeCode }}</span>
                                        </div>
                                    </a>   
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                @auth
                    <a href="{{ route('user.dashboard') }}" class="action-button dashboard-button pl-5 pr-5">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="" id="login-button">{{ __('Sign In') }}</a>

                    @if (config('settings.registration') == 'enabled')
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-2 action-button register-button pl-5 pr-5">{{ __('Sign Up') }}</a>
                        @endif
                    @endif
                @endauth
            </div>
        @endif                
        </div>
    </nav>
</div>