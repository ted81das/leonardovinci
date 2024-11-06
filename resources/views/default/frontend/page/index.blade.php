@extends('layouts.frontend')

@section('metadata')
    <meta name="description" content="{{ __($page->seo_description) }}">
    <meta name="keywords" content="{{ __($page->seo_keywords) }}">    
    <link rel="canonical" href="{{ $page->seo_url }}">
    <title>{{ __($page->seo_title) }}</title>
@endsection

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
                        <span class="fs-10"><a class="" href="{{ url('/') }}">{{ __('Home') }}</a> / <span class="text-muted">{{ __($page->title) }}</span></span>
                        <h1 class="fs-30 mt-2 font-weight-bold text-center">{{ __($page->title) }}</h1>

                    </div> <!-- END SECTION TITLE -->
                </div>
            </div>
        </div>
    </div>

    <section id="page-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-12">  
                    <div class="p-4 pt-7 pb-7">              
                        <div class="mb-9">
                            {!! $page->content !!}
                        </div>  
                    </div>  
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection