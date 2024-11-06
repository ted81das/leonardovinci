<section id="main">
            
    <div class="h-100vh justify-center min-h-screen" id="main-background">

        <div class="container h-100vh ">
            <div class="row h-100vh vertical-center">
                <div class="col-sm-12 upload-responsive">
                    <div class="text-container text-center">
                        <h6 class="mb-4 fs-14" data-aos="fade-up" data-aos-delay="50" data-aos-once="true" data-aos-duration="100"><i class="fa-sharp fa-solid fa-sparkles mr-1"></i> {{ __($frontend_sections->main_banner_pretitle) }}</span></h6>
                        <h1 data-aos="fade-up" data-aos-delay="100" data-aos-once="true" data-aos-duration="200">{{ __($frontend_sections->main_banner_title) }}</span></h1>
                        <div class="word-container" data-aos="fade-up" data-aos-delay="150" data-aos-once="true" data-aos-duration="300">
                            <h2 class="ah-headline">
                              <span class="ah-words-wrapper text-center">
                                @php
                                    $carousel_values = explode(',', $frontend_sections->main_banner_carousel);
                                    $firstKey = array_key_first($carousel_values);
                                    foreach($carousel_values as $key => $value) {
                                        if ($key == $firstKey) {
                                            echo  '<b class="is-visible">' . $value . '</b>';
                                        } else {
                                            echo  '<b>' . $value . '</b>';
                                        }                                       
                                    }                      
                                @endphp
                              </span>
                            </h2>
                          </div>
                        <p class="fs-16" data-aos="fade-up" data-aos-delay="400" data-aos-once="true" data-aos-duration="700">{{ __($frontend_sections->main_banner_subtitle) }}</p>

                        <a href="{{ route('register') }}" class="btn-primary-frontend btn-frontend-scroll-effect mb-2" id="top-main-button"  data-aos="fade-up" data-aos-delay="500" data-aos-once="true" data-aos-duration="800">
                            <div>
                                <span>{{ __('Start Creating for Free') }}</span>
                                <span>{{ __('Start Creating for Free') }}</span>
                            </div>
                        </a>
                        <div>
                            <span class="fs-12" data-aos="fade-up" data-aos-delay="900" data-aos-once="true" data-aos-duration="1300">{{ __('No credit card required') }}</span>
                        </div>
                        <div class="text-center d-flex mx-auto justify-content-center mt-5" data-aos="fade-up" data-aos-delay="600" data-aos-once="true" data-aos-duration="900">
                            <div class="happy-customers">
                                <div class="customer-avatars">
                                    <img src="{{ theme_url('img/frontend/customers/1.png') }}" alt="" class="small-avatar">
                                    <img src="{{ theme_url('img/frontend/customers/2.png') }}" alt="" class="small-avatar overlap">
                                    <img src="{{ theme_url('img/frontend/customers/3.png') }}" alt="" class="small-avatar overlap">
                                    <img src="{{ theme_url('img/frontend/customers/4.png') }}" alt="" class="small-avatar overlap">
                                    <img src="{{ theme_url('img/frontend/customers/5.png') }}" alt="" class="small-avatar overlap">
                                </div>
                                <div>
                                    <span class="fs-12">12,577 {{ __('Happy Customers') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                                
            </div>           
        </div>

    </div> 
    
    <div class="container-fluid" id="curve-container">
        <div class="curve-box">
            <div class="overflow-hidden">
                <svg class="curve" width="1440" height="105" viewBox="0 0 1440 105" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path d="M0 0C240 68.7147 480 103.072 720 103.072C960 103.072 1200 68.7147 1440 0V104.113H0V0Z"></path>
                </svg>
            </div>
        </div>
    </div>
</section>