<section id="info-banner">
    <div class="container">
        
        <!-- SECTION TITLE -->
        <div class="row pl-7">
            <div class="title">
                <h3>{!! __($frontend_sections->info_title) !!}</h3>                     
            </div>
        </div> <!-- END SECTION TITLE -->          

        <div class="row justify-content-center pl-7 pr-7 pt-1 pb-5">
            <div class="col-lg-4 col-md-12 col-sm-12" data-aos="fade-up" data-aos-delay="100" data-aos-once="true" data-aos-duration="400">
                <div class="info-box mr-3 d-flex">
                    <div class="info-text text-center w-80">
                        <h4>{{__ ('Advanced') }}</h4>
                        <h4>{{__ ('Analytics') }}</h4>
                        <p class="fs-12 mt-2 w-90 mx-auto">{{__('Closely monitor and control your AI usage')}}</p>
                    </div>
                    <div class="info-img text-right w-100">
                        <img src="{{ theme_url('img/frontend/customers/extra-monitoring.webp') }}" alt="">
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 col-sm-12" data-aos="fade-up" data-aos-delay="200" data-aos-once="true" data-aos-duration="500">
                <div class="info-box ml-3 mr-3 team-wrapper">                            
                    <img src="{{ theme_url('img/frontend/customers/extra1.webp') }}" alt="" class="team-image-1">
                    <img src="{{ theme_url('img/frontend/customers/extra2.webp') }}" alt="" class="team-image-2">
                    <img src="{{ theme_url('img/frontend/customers/extra3.webp') }}" alt="" class="team-image-3">
                    <img src="{{ theme_url('img/frontend/customers/extra4.webp') }}" alt="" class="team-image-4">
                    
                    <div class="team-text text-center">
                        <h4>{{__ ('Team') }}</h4>
                        <h4>{{__ ('Management') }}</h4>
                        <p class="fs-12 mt-2 w-90 mx-auto">{{__('Collaborate with your team to create your desired dream content')}}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 col-sm-12" data-aos="fade-up" data-aos-delay="300" data-aos-once="true" data-aos-duration="600">
                <div class="info-box mr-3 d-flex">
                    <div class="info-text pl-4 text-center w-80">
                        <h4>{{__ ('Project') }}</h4>
                        <h4>{{__ ('Management') }}</h4>
                        <p class="fs-12 mt-2 w-90 mx-auto">{{__('Organize your creative projects')}}</p>
                    </div>
                    <div class="info-img text-right w-100">
                        <img src="{{ theme_url('img/frontend/customers/extra-project.webp') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>