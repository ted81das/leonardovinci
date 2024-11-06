<section id="templates-wrapper">

    {!! adsense_frontend_features_728x90() !!}
    

    <div class="container pt-9 text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-6">
            <div class="title">
                <p class="m-2">{{ __($frontend_sections->features_subtitle) }}</p>
                <h3>{!! __($frontend_sections->features_title) !!}</h3>                        
            </div>
        </div> <!-- END SECTION TITLE --> 
                      
    </div> <!-- END CONTAINER -->

    <div class="container">    
            
        <div class="row">

            <div class="col-lg-12 col-md-12 col-sm-12" data-aos="zoom-in" data-aos-delay="100" data-aos-once="true" data-aos-duration="400">                
                <div class="templates-nav-menu">
                    <div class="template-nav-menu-inner">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">{{ __('All Templates') }}</button>
                            </li>
                            @foreach ($categories as $category)
                                @if (strtolower($category->name) != 'other')
                                    <li class="nav-item category-check" role="presentation">
                                        <button class="nav-link" id="{{ $category->code }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $category->code }}" type="button" role="tab" aria-controls="{{ $category->code }}" aria-selected="false">{{ __($category->name) }}</button>
                                    </li>
                                @endif									
                            @endforeach	
                            @foreach ($categories as $category)
                            @if (strtolower($category->name) == 'other')
                                <li class="nav-item category-check" role="presentation">
                                    <button class="nav-link" id="{{ $category->code }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $category->code }}" type="button" role="tab" aria-controls="{{ $category->code }}" aria-selected="false">{{ __($category->name) }}</button>
                                </li>
                            @endif									
                        @endforeach				
                        </ul>
                    </div>
                </div>					
            </div>
    
            <div class="col-lg-12 col-md-12 col-sm-12 ">
                <div class="pt-2">
                    <div class="favorite-templates-panel show-templates">
    
                        <div class="tab-content" id="myTabContent">
    
                            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                                <div class="row templates-panel">
                                    @foreach ($categories as $category)
                                        @if (strtolower($category->name) != 'other')
                                            <div class="col-12 templates-panel-group @if($loop->first) @else  mt-3 @endif">
                                                <h6 class="fs-14 font-weight-bold text-muted">{{ __($category->name) }}</h6>
                                                <h4 class="fs-12 text-muted">{{ __($category->description) }}</h4>
                                            </div>						
                    
                                            @foreach ($other_templates as $template)
                                                @if ($template->group == $category->code)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="template">                                                                        
                                                            <div class="card @if($template->package == 'professional') professional @elseif($template->package == 'premium') premium @elseif($template->favorite) favorite @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('app/user/templates/original-template') }}/{{ $template->slug }}'">
                                                                <div class="card-body pt-5">
                                                                    <div class="template-icon mb-4">
                                                                        {!! $template->icon !!}												
                                                                    </div>
                                                                    <div class="template-title">
                                                                        <h6 class="mb-2 fs-15 number-font">{{ __($template->name) }}</h6>
                                                                    </div>
                                                                    <div class="template-info">
                                                                        <p class="fs-13 text-muted mb-2">{{ __($template->description) }}</p>
                                                                    </div>
                                                                    @if($template->package == 'professional') 
                                                                        <p class="fs-8 btn btn-pro mb-0"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-pro"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'free')
                                                                        <p class="fs-8 btn btn-free mb-0"><i class="fa-sharp fa-solid fa-gift mr-2"></i>{{ __('Free') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-free"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'premium')
                                                                        <p class="fs-8 btn btn-yellow mb-0"><i class="fa-sharp fa-solid fa-gem mr-2"></i>{{ __('Premium') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-premium"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->new)
                                                                        <span class="fs-8 btn btn-new mb-0"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</span>
                                                                    @endif		
                                                                </div>
                                                            </div>
                                                        </div>							
                                                    </div>
                                                @endif
                                            @endforeach	
                                            
                                            @foreach ($custom_templates as $template)
                                                @if ($template->group == $category->code)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="template">                                                                       
                                                            <div class="card @if($template->package == 'professional') professional @elseif($template->package == 'premium') premium @elseif($template->favorite) favorite @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('app/user/templates') }}/{{ $template->slug }}/{{ $template->template_code }}'">
                                                                <div class="card-body pt-5">
                                                                    <div class="template-icon mb-4">
                                                                        {!! $template->icon !!}												
                                                                    </div>
                                                                    <div class="template-title">
                                                                        <h6 class="mb-2 fs-15 number-font">{{ __($template->name) }}</h6>
                                                                    </div>
                                                                    <div class="template-info">
                                                                        <p class="fs-13 text-muted mb-2">{{ __($template->description) }}</p>
                                                                    </div>
                                                                    @if($template->package == 'professional') 
                                                                        <p class="fs-8 btn btn-pro mb-0"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-pro"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'free')
                                                                        <p class="fs-8 btn btn-free mb-0"><i class="fa-sharp fa-solid fa-gift mr-2"></i>{{ __('Free') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-free"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'premium')
                                                                        <p class="fs-8 btn btn-yellow mb-0"><i class="fa-sharp fa-solid fa-gem mr-2"></i>{{ __('Premium') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-premium"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->new)
                                                                        <span class="fs-8 btn btn-new mb-0"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</span>
                                                                    @endif	
                                                                </div>
                                                            </div>
                                                        </div>							
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif	
                                    @endforeach		
    
                                    @foreach ($categories as $category)
                                        @if (strtolower($category->name) == 'other')
                                            <div class="col-12 templates-panel-group @if($loop->first) @else  mt-3 @endif">
                                                <h6 class="fs-14 font-weight-bold text-muted">{{ __($category->name) }}</h6>
                                                <h4 class="fs-12 text-muted">{{ __($category->description) }}</h4>
                                            </div>					
                    
                                            @foreach ($other_templates as $template)
                                                @if ($template->group == $category->code)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="template">                                                                        
                                                            <div class="card @if($template->package == 'professional') professional @elseif($template->package == 'premium') premium @elseif($template->favorite) favorite @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('app/user/templates/original-template') }}/{{ $template->slug }}'">
                                                                <div class="card-body pt-5">
                                                                    <div class="template-icon mb-4">
                                                                        {!! $template->icon !!}												
                                                                    </div>
                                                                    <div class="template-title">
                                                                        <h6 class="mb-2 fs-15 number-font">{{ __($template->name) }}</h6>
                                                                    </div>
                                                                    <div class="template-info">
                                                                        <p class="fs-13 text-muted mb-2">{{ __($template->description) }}</p>
                                                                    </div>
                                                                    @if($template->package == 'professional') 
                                                                        <p class="fs-8 btn btn-pro mb-0"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-pro"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'free')
                                                                        <p class="fs-8 btn btn-free mb-0"><i class="fa-sharp fa-solid fa-gift mr-2"></i>{{ __('Free') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-free"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'premium')
                                                                        <p class="fs-8 btn btn-yellow mb-0"><i class="fa-sharp fa-solid fa-gem mr-2"></i>{{ __('Premium') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-premium"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->new)
                                                                        <span class="fs-8 btn btn-new mb-0"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</span>
                                                                    @endif	
                                                                </div>
                                                            </div>
                                                        </div>							
                                                    </div>
                                                @endif
                                            @endforeach	
                                            
                                            @foreach ($custom_templates as $template)
                                                @if ($template->group == $category->code)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="template">                                                                      
                                                            <div class="card @if($template->package == 'professional') professional @elseif($template->package == 'premium') premium @elseif($template->favorite) favorite @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('app/user/templates') }}/{{ $template->slug }}/{{ $template->template_code }}'">
                                                                <div class="card-body pt-5">
                                                                    <div class="template-icon mb-4">
                                                                        {!! $template->icon !!}												
                                                                    </div>
                                                                    <div class="template-title">
                                                                        <h6 class="mb-2 fs-15 number-font">{{ __($template->name) }}</h6>
                                                                    </div>
                                                                    <div class="template-info">
                                                                        <p class="fs-13 text-muted mb-2">{{ __($template->description) }}</p>
                                                                    </div>
                                                                    @if($template->package == 'professional') 
                                                                        <p class="fs-8 btn btn-pro mb-0"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-pro"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'free')
                                                                        <p class="fs-8 btn btn-free mb-0"><i class="fa-sharp fa-solid fa-gift mr-2"></i>{{ __('Free') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-free"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->package == 'premium')
                                                                        <p class="fs-8 btn btn-yellow mb-0"><i class="fa-sharp fa-solid fa-gem mr-2"></i>{{ __('Premium') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-premium"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                    @elseif($template->new)
                                                                        <span class="fs-8 btn btn-new mb-0"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</span>
                                                                    @endif	
                                                                </div>
                                                            </div>
                                                        </div>							
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif	
                                    @endforeach	
                                </div>	
                            </div>
    
                            @foreach ($categories as $category)
                                <div class="tab-pane fade" id="{{ $category->code }}" role="tabpanel" aria-labelledby="{{ $category->code }}-tab">
                                    <div class="row templates-panel">
                
                                        @foreach ($other_templates as $template)
                                            @if ($template->group == $category->code)
                                                <div class="col-lg-4 col-md-6 col-sm-12">
                                                    <div class="template">                                                                    
                                                        <div class="card @if($template->package == 'professional') professional @elseif($template->package == 'premium') premium @elseif($template->favorite) favorite @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('app/user/templates/original-template') }}/{{ $template->slug }}'">
                                                            <div class="card-body pt-5">
                                                                <div class="template-icon mb-4">
                                                                    {!! $template->icon !!}												
                                                                </div>
                                                                <div class="template-title">
                                                                    <h6 class="mb-2 fs-15 number-font">{{ __($template->name) }}</h6>
                                                                </div>
                                                                <div class="template-info">
                                                                    <p class="fs-13 text-muted mb-2">{{ __($template->description) }}</p>
                                                                </div>
                                                                @if($template->package == 'professional') 
                                                                    <p class="fs-8 btn btn-pro mb-0"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-pro"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                @elseif($template->package == 'free')
                                                                    <p class="fs-8 btn btn-free mb-0"><i class="fa-sharp fa-solid fa-gift mr-2"></i>{{ __('Free') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-free"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                @elseif($template->package == 'premium')
                                                                    <p class="fs-8 btn btn-yellow mb-0"><i class="fa-sharp fa-solid fa-gem mr-2"></i>{{ __('Premium') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-premium"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                @elseif($template->new)
                                                                    <span class="fs-8 btn btn-new mb-0"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</span>
                                                                @endif	
                                                            </div>
                                                        </div>
                                                    </div>							
                                                </div>	
                                            @endif									
                                        @endforeach		
    
                                        @foreach ($custom_templates as $template)
                                            @if ($template->group == $category->code)
                                                <div class="col-lg-4 col-md-6 col-sm-12">
                                                    <div class="template">                                                                   
                                                        <div class="card @if($template->package == 'professional') professional @elseif($template->package == 'premium') premium @elseif($template->favorite) favorite @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('app/user/templates') }}/{{ $template->slug }}/{{ $template->template_code }}'">
                                                            <div class="card-body pt-5">
                                                                <div class="template-icon mb-4">
                                                                    {!! $template->icon !!}												
                                                                </div>
                                                                <div class="template-title">
                                                                    <h6 class="mb-2 fs-15 number-font">{{ __($template->name) }}</h6>
                                                                </div>
                                                                <div class="template-info">
                                                                    <p class="fs-13 text-muted mb-2">{{ __($template->description) }}</p>
                                                                </div>
                                                                @if($template->package == 'professional') 
                                                                    <p class="fs-8 btn btn-pro mb-0"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-pro"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                @elseif($template->package == 'free')
                                                                    <p class="fs-8 btn btn-free mb-0"><i class="fa-sharp fa-solid fa-gift mr-2"></i>{{ __('Free') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-free"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                @elseif($template->package == 'premium')
                                                                    <p class="fs-8 btn btn-yellow mb-0"><i class="fa-sharp fa-solid fa-gem mr-2"></i>{{ __('Premium') }} @if($template->new) <p class="fs-8 btn btn-new mb-0 btn-new-premium"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</p> @endif</p> 
                                                                @elseif($template->new)
                                                                    <span class="fs-8 btn btn-new mb-0"><i class="fa-sharp fa-solid fa-sparkles mr-2"></i>{{ __('New') }}</span>
                                                                @endif	
                                                            </div>
                                                        </div>
                                                    </div>							
                                                </div>
                                            @endif
                                        @endforeach	
                                    </div>
                                </div>
                            @endforeach	
                        
    
                        </div>
                        
                        <div class="show-templates-button">
                            <a href="#">
                                <span>{{ __('Show More') }} <i class="ml-2 fs-10 fa-solid fa-chevrons-down"></i></span>
                                <span>{{ __('Show Less') }} <i class="ml-2 fs-10 fa-solid fa-chevrons-up"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    
        </div>


    </div>

</section>