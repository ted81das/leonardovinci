<section id="steps-wrapper">

    <div class="container pt-9 text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-6">
            <div class="title">
                <p class="m-2">{{ __($frontend_sections->how_it_works_subtitle) }}</p>
                <h3>{!! __($frontend_sections->how_it_works_title) !!}</h3>                         
            </div>
        </div> <!-- END SECTION TITLE --> 
                      
    </div> <!-- END CONTAINER -->

    <div class="container">

        <div class="row">
            @foreach ($steps as $step)
                <div class="col-lg-4 col-md-12 col-sm-12" data-aos="fade-up" data-aos-delay="{{ 100 * $step->order }}" data-aos-once="true" data-aos-duration="400">
                    <div class="steps-box-wrapper">
                        <div class="steps-box">
                            <div class="step-number-big">
                                <p>{{ $step->order }}</p>
                            </div>
                            <div class="step-number">
                                <h6>{{ __('Step') }} {{ $step->order }}</h6>
                            </div>
                            <div class="step-title">
                                <h2>{{ __($step->title) }}</h2>
                            </div>
                            <div class="step-description">
                                <p>{!! __($step->description) !!}</p>
                            </div>
                        </div>
                    </div>                        
                </div>
            @endforeach
        </div>

    </div>
    
</section>