<section id="faqs">   

    <div class="container pt-9 text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-7">
            <div class="title">
                <p class="m-2">{{ __($frontend_sections->faq_subtitle) }}</p>
                <h3 class="mb-4">{!! __($frontend_sections->faq_title) !!}</h3> 
                <h6 class="font-weight-normal fs-14 text-center">{{ __($frontend_sections->faq_description) }}</h6>                        
            </div>
        </div> <!-- END SECTION TITLE --> 
                        
    </div> <!-- END CONTAINER -->

    <div class="container">

        <div class="row">

            @if ($faq_exists)                          

                @foreach ( $faqs as $faq )

                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div id="accordion" data-aos="fade-left" data-aos-delay="200" data-aos-once="true" data-aos-duration="700">
                            <div class="card">
                                <div class="card-header" id="heading{{ $faq->id }}">
                                    <h5 class="mb-0">
                                    <span class="btn btn-link faq-button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $faq->id }}" aria-expanded="false" aria-controls="collapse-{{ $faq->id }}">
                                        <i class="fa-solid fa-messages-question fs-14 text-muted mr-2"></i> {{ __($faq->question) }}
                                    </span>
                                    </h5>
                                    <i class="fa-solid fa-plus fs-10"></i>
                                </div>
                            
                                <div id="collapse-{{ $faq->id }}" class="collapse" aria-labelledby="heading{{ $faq->id }}" data-bs-parent="#accordion">
                                    <div class="card-body">
                                        {!! __($faq->answer) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endforeach

            @else
                <div class="row text-center">
                    <div class="col-sm-12 mt-6 mb-6">
                        <h6 class="fs-12 font-weight-bold text-center">{{ __('No FAQ answers were published yet') }}</h6>
                    </div>
                </div>
            @endif

        </div>        
    </div>

</section> 