<section id="benefits-wrapper">

    <div class="container pt-9"> 
        
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-5" data-aos="fade-right" data-aos-delay="100" data-aos-once="true" data-aos-duration="400">                        
                <div class="title">
                    <p class="m-2">{{ __($frontend_sections->features_subtitle) }}</p>
                    <h3>{!! __($frontend_sections->features_title) !!}</h3>    
                    <h6 class="font-weight-normal fs-14 mb-4">{{ __($frontend_sections->features_description) }}</h6>                    
                    <a href="{{ route('register') }}" class="btn-primary-frontend-small btn-frontend-scroll-effect mb-2">
                        <div>
                            <span>{{ __('Try Creating for Free') }}</span>
                            <span>{{ __('Try Creating for Free') }}</span>
                        </div>
                    </a>
                </div>                                               
            </div>

            @foreach ($features as $feature)
                <div class="col-lg-4 col-md-6 col-sm-12 mb-5" data-aos="zoom-in" data-aos-delay="{{  (200 * $feature->id)/2 }}" data-aos-once="true" data-aos-duration="500">
                    <div class="benefits-box-wrapper text-center">
                        <div class="benefit-box">
                            <div class="benefit-image">
                                <img src="{{ theme_url($feature->image) }}" alt="">
                            </div>
                            <div class="benefit-title">
                                <h6>{!! __($feature->title) !!}</h6>
                            </div>
                            <div class="benefit-description">
                                <p>{!! __($feature->description) !!}</p>
                            </div>
                        </div>
                    </div>                        
                </div> 
            @endforeach                                       
        </div>
    </div>

</section>