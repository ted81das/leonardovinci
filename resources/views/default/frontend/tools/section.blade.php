<section id="features">

    {!! adsense_frontend_features_728x90() !!}
    
    <div class="container pt-7 text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-6">
            <div class="title">
                <p class="m-2">{{ __($frontend_sections->tools_subtitle) }}</p>
                <h3>{!! __($frontend_sections->tools_title) !!}</h3>                        
            </div>
        </div> <!-- END SECTION TITLE --> 
                        
    </div> <!-- END CONTAINER -->


    <div class="container">    
        
        <div class="row">

            <div class="col-lg-12 col-md-12 col-sm-12" data-aos="zoom-in" data-aos-delay="100" data-aos-once="true" data-aos-duration="400">                
                <div class="features-nav-menu">
                    <div class="features-nav-menu-inner">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            @foreach ($tools as $tool)
                                @if ($tool->status)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if ($loop->first) active @endif" id="{{ $tool->tool_code }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $tool->tool_code }}" type="button" role="tab" aria-controls="{{ $tool->tool_code }}" aria-selected="true">{{ __($tool->tool_name) }}</button>
                                    </li>
                                @endif                                            
                            @endforeach                                    
                        </ul>
                    </div>
                </div>					
            </div>
    
            <div class="col-lg-12 col-md-12 col-sm-12 ">
                <div class="pt-6">
                    <div class="features-panel">
    
                        <div class="tab-content" id="myTabContent">
    
                            @foreach ($tools as $tool)

                                <div class="tab-pane fade  @if ($loop->first) show active @endif" id="{{ $tool->tool_code }}" role="tabpanel" aria-labelledby="{{ $tool->tool_code }}">  
                                    <div class="row features-outer-wrapper">

                                        <div class="col-lg-6 col-md-6 col-sm-12 pl-6 pr-6 align-middle" data-aos="fade-right" data-aos-delay="200" data-aos-once="true" data-aos-duration="500">                                                    
                                            <div class="features-inner-wrapper text-center">                                                                   
                                            
                                                <div class="feature-title">
                                                    <h6 class="fs-12 mb-5"><i class="fa-solid mr-2 {{ $tool->title_icon }}"></i>{{ __($tool->title_meta) }}</h6>
                                                    <h4 class="mb-5 fs-30">{!! __($tool->title) !!}</h4>                                                            
                                                </div>	

                                                <div class="feature-description">
                                                    <p class="mb-6">{!! __($tool->description) !!}</p>
                                                </div>                                                            
                                            </div>                                                                                                  						
                                        </div>	

                                        <div class="col-lg-6 col-md-6 col-sm-12" data-aos="fade-left" data-aos-delay="300" data-aos-once="true" data-aos-duration="600">
                                            <div class="feature-image-wrapper">
                                                <img src="{{ theme_url($tool->image) }}" alt="">
                                            </div>
                                            <div class="feature-footer text-center">
                                                <p class="fs-12 text-muted">{{ __($tool->image_footer) }}</p>
                                            </div>
                                        </div>
        
                                    </div>	
                                </div>
                            @endforeach
                            
                        </div>                                    
                    </div>
                </div>
            </div>
    
        </div>            

    </div>

</section>
        