@extends('layouts.auth')

@section('metadata')
    <meta name="description" content="{{ __($metadata->register_description) }}">
    <meta name="keywords" content="{{ __($metadata->register_keywords) }}">
    <meta name="author" content="{{ __($metadata->register_author) }}">	    
    <link rel="canonical" href="{{ $metadata->register_url }}">
    <title>{{ __($metadata->register_title) }}</title>
@endsection

@section('css')
	<!-- Data Table CSS -->
	<link href="{{URL::asset('plugins/awselect/awselect.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
    @if (config('frontend.maintenance') == 'on')			
        <div class="container h-100vh">
            <div class="row text-center h-100vh align-items-center">
                <div class="col-md-12">
                    <img src="{{ theme_url('img/files/maintenance.png') }}" alt="Maintenance Image">
                    <h2 class="mt-4 font-weight-bold">{{ __('We are just tuning up a few things') }}.</h2>
                    <h5>{{ __('We apologize for the inconvenience but') }} <span class="font-weight-bold text-info">{{ config('app.name') }}</span> {{ __('is currenlty undergoing planned maintenance') }}.</h5>
                </div>
            </div>
        </div>
    @else
        @if (config('settings.registration') == 'enabled')
            <div class="container-fluid h-100vh ">                
                <div class="row login-background justify-content-center">

                    <div class="col-sm-12" id="login-responsive"> 
                        <div class="row justify-content-center subscribe-registration-background">
                            <div class="col-lg-8 col-md-12 col-sm-12 mx-auto">
                                <div class="card-body pt-8">

                                    <a class="navbar-brand register-logo" href="{{ url('/') }}"><img id="brand-img"  src="{{ URL::asset($settings->logo_frontend) }}" alt=""></a>
                                    
                                    <div class="registration-nav mb-8 mt-8">
                                        <div class="registration-nav-inner">					
                                            <div class="row text-center justify-content-center">
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="d-flex wizard-nav-text">
                                                        <div class="wizard-step-number current-step mr-3 fs-14" id="step-one-number"><i class="fa-solid fa-check"></i></div>
                                                        <div class="wizard-step-title"><span class="font-weight-bold fs-14">{{ __('Create Account') }}</span> <br> <span class="text-muted wizard-step-title-number fs-11 float-left">{{ __('STEP 1') }}</span></div>
                                                    </div>
                                                    <div>
                                                        <i class="fa-solid fa-chevrons-right wizard-nav-chevron current-sign" id="step-one-icon"></i>
                                                    </div>									
                                                </div>	
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="d-flex wizard-nav-text">
                                                        <div class="wizard-step-number mr-3 fs-14 current-step" id="step-two-number"><i class="fa-solid fa-check"></i></div>
                                                        <div class="wizard-step-title responsive"><span class="font-weight-bold fs-14">{{ __('Select Your Plan') }}</span> <br> <span class="text-muted wizard-step-title-number fs-11 float-left">{{ __('STEP 2') }}</span></div>
                                                    </div>	
                                                    <div>
                                                        <i class="fa-solid fa-chevrons-right wizard-nav-chevron current-sign" id="step-two-icon"></i>
                                                    </div>								
                                                </div>
                                                <div class="col-lg-3 col-sm-12">
                                                    <div class="d-flex wizard-nav-text">
                                                        <div class="wizard-step-number mr-3 fs-14 current-step" id="step-three-number"><i class="fa-solid fa-check"></i></div>
                                                        <div class="wizard-step-title"><span class="font-weight-bold fs-14">{{ __('Payment') }}</span> <br> <span class="text-muted wizard-step-title-number fs-11 float-left">{{ __('STEP 3') }}</span></div>
                                                    </div>								
                                                </div>
                                            </div>					
                                        </div>
                                    </div>                                


                                    <div id="payment" class="subscribe-third-step">

                                        <h3 class="text-center login-title mb-2">{{__('Registration Completed')}} </h3>
                                        <p class="fs-12 text-muted text-center mb-8">{{ __('Thank you for registering with us') }}</p>

                                        <div class="row justify-content-center">
                                            <div class="col-lg-8 col-md-12 col-sm-12">                                                
                                                <h5 class="text-center font-weight-bold mb-2">{{__('Payment was successfully processed')}} </h5>  
                                                <p class="fs-12 text-muted text-center mb-8">{{ __('Your account is fully active now.') }}</p> 
                                                
                                                <p class="fs-12 text-muted text-center">{{ __('Go ahead and sign in.') }}</p> 
                                                <div class="text-center">
                                                    <a href="{{ route('login') }}"  class="fs-12 font-weight-bold text-primary special-action-sign">{{ __('Sign In') }}</a> 
                                                </div>                                                                       
                                            </div>
                                        </div>

                                    </div>
                                </div> 
                            </div>      
                        </div>
                    </div>
                </div>
            </div>
        @else
            <h5 class="text-center pt-9">{{__('New user registration is disabled currently')}}</h5>
        @endif
    @endif
@endsection


