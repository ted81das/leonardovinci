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
                        <span class="fs-10"><a class="" href="{{ url('/') }}">{{ __('Blogs') }}</a> / <span class="text-muted">{{ __($blog->title) }}</span></span>
                        <h1 class="fs-30 mt-2 mb-3 font-weight-bold text-center">{{ __($blog->title) }}</h1>
                        <p class="fs-10 text-center text-muted"><span><i class="mdi mdi-account-edit mr-1"></i>{{ $blog->created_by }}</span> / <span><i class="mdi mdi-alarm mr-1"></i>{{ date('F j, Y', strtotime($blog->created_at)) }}</span></p>
                    </div> <!-- END SECTION TITLE -->
                </div>
            </div>
        </div>
    </div>

    <section id="blogs">

        <div class="container">
            <div class="row justify-content-md-center">

                <div class="col-md-12 col-sm-12">
                    <div class="blog mb-7">
                        <img src="{{ theme_url($blog->image) }}" alt="Blog Image" class="main-image">
                    </div>
                    
                    <div class="fs-14 main-text" id="blog-view-mobile">{!! $blog->body !!}</div>
                </div>
     
            </div>        
        </div>

    </section>
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection

