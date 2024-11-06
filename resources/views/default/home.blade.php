@extends('layouts.frontend')

@section('metadata')
    <meta name="description" content="{{ __($metadata->home_description) }}">
    <meta name="keywords" content="{{ __($metadata->home_keywords) }}">
    <meta name="author" content="{{ __($metadata->home_author) }}">	    
    <link rel="canonical" href="{{ $metadata->home_url }}">
    <title>{{ __($metadata->home_title) }}</title>
@endsection

@section('css')
    <link href="{{URL::asset('plugins/slick/slick.css')}}" rel="stylesheet" />
	<link href="{{URL::asset('plugins/slick/slick-theme.css')}}" rel="stylesheet" />
    <link href="{{URL::asset('plugins/aos/aos.css')}}" rel="stylesheet" />
    <link href="{{URL::asset('plugins/animatedheadline/jquery.animatedheadline.css')}}" rel="stylesheet" />
@endsection

@section('menu')
    @include('frontend.menu.section')
@endsection

@section('content')

    <!-- SECTION - MAIN BANNER
    ========================================================-->
    @include('frontend.banner.section')


    <!-- SECTION - STEPS
    ========================================================-->
    @includeWhen($frontend_sections->how_it_works_status == 1, 'frontend.how_it_works.section')
    

    <!-- SECTION - TOOLS
    ========================================================-->
    @includeWhen($frontend_sections->tools_status == 1, 'frontend.tools.section')
    

    <!-- SECTION - INFO BANNER
    ========================================================-->
    @includeWhen($frontend_sections->info_status == 1, 'frontend.info.section')


    <!-- SECTION - TEMPLATES
    ========================================================-->
    @includeWhen($frontend_sections->templates_status == 1, 'frontend.templates.section')


    <!-- SECTION - FEATURES
    ========================================================-->
    @includeWhen($frontend_sections->features_status == 1, 'frontend.features.section')


    <!-- SECTION - IMAGES BANNER
    ========================================================-->
    @includeWhen($frontend_sections->images_status == 1, 'frontend.images.section')
    

    <!-- SECTION - PRICING
    ========================================================-->
    @includeWhen($frontend_sections->pricing_status == 1, 'frontend.pricing.section')


        <!-- SECTION - CLIENTS
    ========================================================-->
    @includeWhen($frontend_sections->clients_status == 1, 'frontend.clients.section')


    <!-- SECTION - REVIEWS
    ========================================================-->
    @includeWhen($frontend_sections->reviews_status == 1, 'frontend.reviews.section')


    <!-- SECTION - FAQ
    ========================================================-->
    @includeWhen($frontend_sections->faq_status == 1, 'frontend.faq.section')


    <!-- SECTION - BLOGS
    ========================================================-->
    @includeWhen($frontend_sections->blogs_status == 1, 'frontend.blogs.section')

@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection


@section('js')
    <script src="{{URL::asset('plugins/slick/slick.min.js')}}"></script>  
    <script src="{{URL::asset('plugins/aos/aos.js')}}"></script> 
    <script src="{{URL::asset('plugins/animatedheadline/jquery.animatedheadline.min.js')}}"></script> 
    <script src="{{theme_url('js/frontend.js')}}"></script> 
    <script type="text/javascript">
		$(function () {

            $('.word-container').animatedHeadline({
                animationType: "slide",
                animationDelay: 2500,
                barAnimationDelay: 3800,
                barWaiting: 800,
                lettersDelay: 50,
                typeLettersDelay: 150,
                selectionDuration: 500,
                typeAnimationDelay: 1300,
                revealDuration: 600,
                revealAnimationDelay: 1500
            });

            AOS.init();

		});    
    </script>
@endsection
        
        
       
        
       
    

