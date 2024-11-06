@extends('layouts.frontend')

@section('menu')
    @include('frontend.menu.page')
@endsection

@section('content')

    <div class="container-fluid secondary-background">
        <div class="row text-center">
            <div class="col-md-12">
                <div class="section-title">
                    <!-- SECTION TITLE -->
                    <div class="text-center mb-9 mt-9 pt-8" id="contact-row">
                        <span class="fs-10"><a class="" href="{{ url('/') }}">{{ __('Home') }}</a> / <span class="text-muted">{{ __('Contact Us') }}</span></span>
                        <h1 class="fs-30 mt-2 mb-2 font-weight-bold text-center">{{ __('Contact Us') }}</h1>
                        <p class="fs-12 text-center text-muted mb-5"><span>{{ __('We are always here right by your side') }}</p>

                    </div> <!-- END SECTION TITLE -->
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION - CONTACT US
    ========================================================-->
    <section id="contact-wrapper">
        <div class="container">

            <div class="row justify-content-md-center">
                <div class="col-sm-12 mb-5">
                    <div>
                        <div class="pt-7 p-0">					
                            <div class="row text-center">
                                <div class="col-lg-4 col-sm-12">
                                    <div class="contact-info-box">
                                        <div class="contact-icon">
                                            <i class="fa-solid fa-location-dot mb-4 fs-25 text-primary"></i>
                                        </div>
                                        <div class="contact-title">
                                            <h6>{{ __('Our Location') }}</h6>
                                            <p>{{ __('Visit us at our local office. We would love to get to know in person.') }}</p>
                                        </div>
                                        <div class="contact-info">
                                            <p class="text-muted mb-0 fs-12">{{ $frontend_sections->contact_location }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-12">
                                    <div class="contact-info-box">
                                        <div class="contact-icon">
                                            <i class="fa-solid fa-envelope mb-4 fs-25 text-primary"></i>
                                        </div>
                                        <div class="contact-title">
                                            <h6>{{ __('Email Us') }}</h6>
                                            <p>{{ __('Drop us an email and you will receive a reply within a short time.') }}</p>
                                        </div>
                                        <div class="contact-info">
                                            <a class="text-muted fs-12" href="mailto:{{ $frontend_sections->contact_email }}" rel="nofollow">{{ $frontend_sections->contact_email }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-12">
                                    <div class="contact-info-box">
                                        <div class="contact-icon">
                                            <i class="fa-solid fa-phone-volume mb-4 fs-25 text-primary"></i>
                                        </div>
                                        <div class="contact-title">
                                            <h6>{{ __('Call Us') }}</h6>
                                            <p>{{ __('Give us a call. Our Experts are ready to talk to you.') }}</p>
                                        </div>
                                        <div class="contact-info">
                                            <a class="text-muted fs-12" href="tel:{{ $frontend_sections->contact_phone }}" rel="nofollow">{{ $frontend_sections->contact_phone }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                       
                </div>      
            </div>     
            
            <div class="row mt-9">                
                        
                <div class="col-md-6 col-sm-12" data-aos="fade-left" data-aos-delay="300" data-aos-once="true" data-aos-duration="700">
                    <img class="w-70" src="{{ theme_url('img/files/about.svg') }}" alt="">
                </div>

                <div class="col-md-6 col-sm-12" data-aos="fade-right" data-aos-delay="300" data-aos-once="true" data-aos-duration="700">
                    <form id="" action="{{ route('contact') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <h6 class="fs-16 font-weight-extra-bold">{{ __('Get in Touch with Us') }}</h6>
                        <p class="fs-14 text-muted">{{ __('Reach out to us at any time and we will be happy to assist you') }}</p>
                        <div class="row justify-content-md-center">
                            <div class="col-md-6 col-sm-12">
                                <div class="input-box mb-4">                             
                                    <input id="name" type="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="off" placeholder="{{ __('First Name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror                            
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="input-box mb-4">                             
                                    <input id="lastname" type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" value="{{ old('lastname') }}" autocomplete="off" placeholder="{{ __('Last Name') }}" required>
                                    @error('lastname')
                                        <span class="invalid-feedback" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror                            
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-md-center">
                            <div class="col-md-6 col-sm-12">
                                <div class="input-box mb-4">                             
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="off"  placeholder="{{ __('Email Address') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror                            
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="input-box mb-4">                             
                                    <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" autocomplete="off"  placeholder="{{ __('Phone Number') }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            {{ $message }}
                                        </span>
                                    @enderror                            
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-md-center">
                            <div class="col-md-12 col-sm-12">
                                <div class="input-box">							
                                    <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="10" required placeholder="{{ __('Message') }}"></textarea>
                                    @error('message')
                                        <p class="text-danger">{{ $errors->first('message') }}</p>
                                    @enderror	
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="recaptcha" id="recaptcha">
                        
                        <div class="row justify-content-md-center text-center">
                            <!-- ACTION BUTTON -->
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary special-action-button">{{ __('Get in Touch') }}</button>							
                            </div>
                        </div>
                    
                    </form>

                </div>                   
                
            </div>
        </div>
    </section>
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection

@section('js')
    @if (config('services.google.recaptcha.enable') == 'on')
        <!-- Google reCaptcha JS -->
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.google.recaptcha.site_key') }}"></script>
        <script>
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.google.recaptcha.site_key') }}', {action: 'contact'}).then(function(token) {
                    if (token) {
                    document.getElementById('recaptcha').value = token;
                    }
                });
            });
        </script>
    @endif
@endsection
