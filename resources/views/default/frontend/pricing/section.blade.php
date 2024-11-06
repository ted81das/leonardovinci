<section id="prices">

    <div class="container pt-9 text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-6">
            <div class="title">
                <p class="m-2">{{ __($frontend_sections->pricing_subtitle) }}</p>
                <h3 class="mb-4">{!! __($frontend_sections->pricing_title) !!}</h3>    
                <h6 class="font-weight-normal fs-14 text-center">{{ __($frontend_sections->pricing_description) }}</h6>                  
            </div>
        </div> <!-- END SECTION TITLE --> 
                        
    </div> <!-- END CONTAINER -->

    <div class="container">
        
        <div class="row">
            <div class="card-body">			

                @if ($monthly || $yearly || $prepaid || $lifetime)
    
                    <div class="tab-menu-heading text-center">
                        <div class="tabs-menu">								
                            <ul class="nav">
                                @if ($prepaid)						
                                    <li><a href="#prepaid" class="@if (!$monthly && !$yearly && $prepaid) active @else '' @endif" data-bs-toggle="tab"> {{ __('Prepaid Packs') }}</a></li>
                                @endif							
                                @if ($monthly)
                                    <li><a href="#monthly_plans" class="@if (($monthly && $prepaid && $yearly) || ($monthly && !$prepaid && !$yearly) || ($monthly && $prepaid && !$yearly) || ($monthly && !$prepaid && $yearly)) active @else '' @endif" data-bs-toggle="tab"> {{ __('Monthly Plans') }}</a></li>
                                @endif	
                                @if ($yearly)
                                    <li><a href="#yearly_plans" class="@if (!$monthly && !$prepaid && $yearly) active @else '' @endif" data-bs-toggle="tab"> {{ __('Yearly Plans') }}</a></li>
                                @endif		
                                @if ($lifetime)
                                    <li><a href="#lifetime" class="@if (!$monthly && !$yearly && !$prepaid &&  $lifetime) active @else '' @endif" data-bs-toggle="tab"> {{ __('Lifetime Plans') }}</a></li>
                                @endif							
                            </ul>
                        </div>
                    </div>
    
                
    
                    <div class="tabs-menu-body">
                        <div class="tab-content">
    
                            @if ($prepaid)
                                <div class="tab-pane @if ((!$monthly && $prepaid) && (!$yearly && $prepaid)) active @else '' @endif" id="prepaid">
    
                                    @if ($prepaids->count())
                                                        
                                        <div class="row justify-content-md-center">
                                        
                                            @foreach ( $prepaids as $prepaid )																			
                                                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="200" data-aos-once="true" data-aos-duration="400">
                                                    <div class="price-card pl-3 pr-3 pt-2 mb-6">
                                                        <div class="card p-4 pl-5 prepaid-cards @if ($prepaid->featured) price-card-border @endif">
                                                            @if ($prepaid->featured)
                                                                <span class="plan-featured-prepaid">{{ __('Most Popular') }}</span>
                                                            @endif
                                                            <div class="plan prepaid-plan">
                                                                <div class="plan-title">{{ $prepaid->plan_name }} </div>
                                                                <div class="plan-cost-wrapper mt-2 text-center mb-3 p-1"><span class="plan-cost">@if (config('payment.decimal_points') == 'allow') {{ number_format((float)$prepaid->price, 2) }} @else {{ number_format($prepaid->price) }} @endif</span><span class="prepaid-currency-sign text-muted">{{ $prepaid->currency }}</span></div>
                                                                <p class="fs-12 mb-3 text-muted">{{ __('Included Credits') }}</p>	
                                                                <div class="credits-box">										 
                                                                    @if ($prepaid->gpt_4_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('GPT 4 Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->gpt_4_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->gpt_4o_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('GPT 4o Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->gpt_4o_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->gpt_4o_mini_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('GPT 4o mini Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->gpt_4o_mini_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->gpt_4_turbo_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('GPT 4 Turbo Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->gpt_4_turbo_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->gpt_3_turbo_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('GPT 3.5 Turbo Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->gpt_3_turbo_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->fine_tune_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Fine Tune Model  Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->fine_tune_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->claude_3_opus_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Claude 3 Opus Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->claude_3_opus_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->claude_3_sonnet_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Claude 3.5 Sonnet Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->claude_3_sonnet_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->claude_3_haiku_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Claude 3 Haiku Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->claude_3_haiku_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->gemini_pro_credits_prepaid != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Gemini Pro Model Words') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->gemini_pro_credits_prepaid) }}</span></p>@endif
                                                                    @if ($prepaid->dalle_images != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Dalle Images Included') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->dalle_images) }}</span></p>@endif
                                                                    @if ($prepaid->sd_images != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('SD Images Included') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->sd_images) }}</span></p>@endif
                                                                    @if ($prepaid->characters != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Characters Included') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->characters) }}</span></p>@endif																							
                                                                    @if ($prepaid->minutes != 0) <p class="fs-12 mt-2 mb-0"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ __('Minutes Included') }}: <span class="ml-2 font-weight-bold text-primary">{{ number_format($prepaid->minutes) }}</span></p>@endif	
                                                                </div>
                                                                <div class="text-center action-button mt-2 mb-2">
                                                                    <a href="{{ route('user.prepaid.checkout', ['type' => 'prepaid', 'id' => $prepaid->id]) }}" class="btn btn-primary-pricing">{{ __('Select Package') }}</a> 
                                                                </div>																								                                                                          
                                                            </div>							
                                                        </div>	
                                                    </div>							
                                                </div>										
                                            @endforeach						
    
                                        </div>
    
                                    @else
                                        <div class="row text-center">
                                            <div class="col-sm-12 mt-6 mb-6">
                                                <h6 class="fs-12 font-weight-bold text-center">{{ __('No Prepaid plans were set yet') }}</h6>
                                            </div>
                                        </div>
                                    @endif
    
                                </div>			
                            @endif	
    
                            @if ($monthly)	
                                <div class="tab-pane @if (($monthly && $prepaid) || ($monthly && !$prepaid) || ($monthly && !$yearly)) active @else '' @endif" id="monthly_plans">
    
                                    @if ($monthly_subscriptions->count())		
    
                                        <div class="row justify-content-md-center">
    
                                            @foreach ( $monthly_subscriptions as $subscription )																			
                                                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="200" data-aos-once="true" data-aos-duration="400">
                                                    <div class="pt-2 ml-2 mr-2 h-100 prices-responsive">
                                                        <div class="card p-5 mb-4 pl-6 pr-6 h-100 price-card @if ($subscription->featured) price-card-border @endif">
                                                            @if ($subscription->featured)
                                                                <span class="plan-featured">{{ __('Most Popular') }}</span>
                                                            @endif
                                                            <div class="plan">			
                                                                <div class="plan-title">{{ $subscription->plan_name }}</div>	
                                                                <p class="plan-cost mb-5">																					
                                                                    @if ($subscription->free)
                                                                        {{ __('Free') }}
                                                                    @else
                                                                        {!! config('payment.default_system_currency_symbol') !!}@if(config('payment.decimal_points') == 'allow'){{ number_format((float)$subscription->price, 2) }} @else{{ number_format($subscription->price) }} @endif<span class="fs-12 text-muted"><span class="mr-1">/</span> {{ __('per month') }}</span>
                                                                    @endif   
                                                                </p>                                                                         
                                                                <div class="text-center action-button mt-2 mb-5">
                                                                    <a href="{{ route('user.plan.subscribe', $subscription->id) }}" class="btn btn-primary-pricing">{{ __('Subscribe Now') }}</a>                                               														
                                                                </div>
                                                                <p class="fs-12 mb-3 text-muted">{{ __('Included Features') }}</p>																		
                                                                <ul class="fs-12 pl-3">	
                                                                    @if ($subscription->gpt_4_turbo_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4T Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4_turbo_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4T') }} {{ number_format($subscription->gpt_4_turbo_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4 Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4') }} {{ number_format($subscription->gpt_4_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4o_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4o Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4o_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4o') }} {{ number_format($subscription->gpt_4o_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4o_mini_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4o mini Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4o_mini_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4o mini') }} {{ number_format($subscription->gpt_4o_mini_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_3_turbo_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 3.5T Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_3_turbo_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 3.5T') }} {{ number_format($subscription->gpt_3_turbo_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->fine_tune_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Fine Tune Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->fine_tune_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Fine Tune Model') }} {{ number_format($subscription->fine_tune_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_opus_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3 Opus Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_opus_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3 Opus Model') }} {{ number_format($subscription->claude_3_opus_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_sonnet_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3.5 Sonnet Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_sonnet_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3.5 Sonnet Model') }} {{ number_format($subscription->claude_3_sonnet_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_haiku_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3 Haiku Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_haiku_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3 Haiku Model') }} {{ number_format($subscription->claude_3_haiku_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gemini_pro_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Gemini Pro Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gemini_pro_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Gemini Pro Model') }} {{ number_format($subscription->gemini_pro_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if ($subscription->dalle_image_engine != 'none')
                                                                            @if ($subscription->dalle_images == -1)
                                                                                <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Dalle images') }}</span></li>
                                                                            @else
                                                                                @if($subscription->dalle_images != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->dalle_images) }}</span> <span class="plan-feature-text">{{ __('Dalle images') }}</span></li> @endif
                                                                            @endif
                                                                        @endif																
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if ($subscription->sd_image_engine != 'none')
                                                                            @if ($subscription->sd_images == -1)
                                                                                <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('SD images') }}</span></li>
                                                                            @else
                                                                                @if($subscription->sd_images != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->sd_images) }}</span> <span class="plan-feature-text">{{ __('SD images') }}</span></li> @endif
                                                                            @endif
                                                                        @endif																	
                                                                    @endif
                                                                    @if (config('settings.whisper_feature_user') == 'allow')
                                                                        @if ($subscription->minutes == -1)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('minutes') }}</span></li>
                                                                        @else
                                                                            @if($subscription->minutes != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->minutes) }}</span> <span class="plan-feature-text">{{ __('minutes') }}</span></li> @endif
                                                                        @endif																	
                                                                    @endif
                                                                    @if (config('settings.voiceover_feature_user') == 'allow')
                                                                        @if ($subscription->characters == -1)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('characters') }}</span></li>
                                                                        @else
                                                                            @if($subscription->characters != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->characters) }}</span> <span class="plan-feature-text">{{ __('characters') }}</span></li> @endif
                                                                        @endif																	
                                                                    @endif
                                                                        @if($subscription->team_members != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->team_members) }}</span> <span class="plan-feature-text">{{ __('team members') }}</span></li> @endif
                                                                    
                                                                    @if (config('settings.writer_feature_user') == 'allow')
                                                                        @if($subscription->writer_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Writer Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.wizard_feature_user') == 'allow')
                                                                        @if($subscription->wizard_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Article Wizard Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.smart_editor_feature_user') == 'allow')
                                                                        @if($subscription->smart_editor_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Smart Editor Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.rewriter_feature_user') == 'allow')
                                                                        @if($subscription->rewriter_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI ReWriter Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_feature_user') == 'allow')
                                                                        @if($subscription->chat_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Chats Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if($subscription->image_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Images Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.voiceover_feature_user') == 'allow')
                                                                        @if($subscription->voiceover_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Voiceover Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.video_feature_user') == 'allow')
                                                                        @if($subscription->video_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Video Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.voice_clone_feature_user') == 'allow')
                                                                        @if($subscription->voice_clone_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Voice Clone Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.sound_studio_feature_user') == 'allow')
                                                                        @if($subscription->sound_studio_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Sound Studio Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.whisper_feature_user') == 'allow')
                                                                        @if($subscription->transcribe_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Speech to Text Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.plagiarism_checker_feature_user') == 'allow')
                                                                        @if($subscription->plagiarism_checker_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Plagiarism Checker Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.vision_feature_user') == 'allow')
                                                                        @if($subscription->vision_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Vision Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.ai_detector_feature_user') == 'allow')
                                                                        @if($subscription->ai_detector_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Detector Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_file_feature_user') == 'allow')
                                                                        @if($subscription->chat_file_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI File Chat Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_web_feature_user') == 'allow')
                                                                        @if($subscription->chat_web_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Web Chat Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.code_feature_user') == 'allow')
                                                                        @if($subscription->code_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Code Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if($subscription->team_members) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Team Members Option') }}</span></li> @endif
                                                                    @foreach ( (explode(',', $subscription->plan_features)) as $feature )
                                                                        @if ($feature)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ $feature }}</li>
                                                                        @endif																
                                                                    @endforeach															
                                                                </ul>																
                                                            </div>					
                                                        </div>	
                                                    </div>							
                                                </div>										
                                            @endforeach
    
                                        </div>	
                                    
                                    @else
                                        <div class="row text-center">
                                            <div class="col-sm-12 mt-6 mb-6">
                                                <h6 class="fs-12 font-weight-bold text-center">{{ __('No Subscriptions plans were set yet') }}</h6>
                                            </div>
                                        </div>
                                    @endif					
                                </div>	
                            @endif	
                            
                            @if ($yearly)	
                                <div class="tab-pane @if (($yearly && $prepaid) && ($yearly && !$prepaid) && ($yearly && !$monthly)) active @else '' @endif" id="yearly_plans">
    
                                    @if ($yearly_subscriptions->count())		
    
                                        <div class="row justify-content-md-center">
    
                                            @foreach ( $yearly_subscriptions as $subscription )																			
                                                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="200" data-aos-once="true" data-aos-duration="400">
                                                    <div class="pt-2 ml-2 mr-2 h-100 prices-responsive">
                                                        <div class="card p-5 mb-4 pl-6 pr-6 h-100 price-card @if ($subscription->featured) price-card-border @endif">
                                                            @if ($subscription->featured)
                                                                <span class="plan-featured">{{ __('Most Popular') }}</span>
                                                            @endif
                                                            <div class="plan">			
                                                                <div class="plan-title">{{ $subscription->plan_name }}</div>																						
                                                                <p class="plan-cost mb-5">
                                                                    @if ($subscription->free)
                                                                        {{ __('Free') }}
                                                                    @else
                                                                        {!! config('payment.default_system_currency_symbol') !!}@if(config('payment.decimal_points') == 'allow'){{ number_format((float)$subscription->price, 2) }} @else{{ number_format($subscription->price) }} @endif<span class="fs-12 text-muted"><span class="mr-1">/</span> {{ __('per year') }}</span>
                                                                    @endif    
                                                                </p>                                                                            
                                                                <div class="text-center action-button mt-2 mb-5">
                                                                    <a href="{{ route('user.plan.subscribe', $subscription->id) }}" class="btn btn-primary-pricing">{{ __('Subscribe Now') }}</a>                                               														
                                                                </div>
                                                                <p class="fs-12 mb-3 text-muted">{{ __('Included Features') }}</p>																	
                                                                <ul class="fs-12 pl-3">	
                                                                    @if ($subscription->gpt_4_turbo_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4T Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4_turbo_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4T') }} {{ number_format($subscription->gpt_4_turbo_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4 Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4') }} {{ number_format($subscription->gpt_4_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4o_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4o Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4o_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4o') }} {{ number_format($subscription->gpt_4o_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4o_mini_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4o  miniModel') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4o_mini_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4o mini') }} {{ number_format($subscription->gpt_4o_mini_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_3_turbo_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 3.5T Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_3_turbo_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 3.5T') }} {{ number_format($subscription->gpt_3_turbo_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->fine_tune_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Fine Tune Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->fine_tune_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Fine Tune Model') }} {{ number_format($subscription->fine_tune_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_opus_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3 Opus Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_opus_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3 Opus Model') }} {{ number_format($subscription->claude_3_opus_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_sonnet_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3.5 Sonnet Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_sonnet_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3.5 Sonnet Model') }} {{ number_format($subscription->claude_3_sonnet_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_haiku_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3 Haiku Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_haiku_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3 Haiku Model') }} {{ number_format($subscription->claude_3_haiku_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gemini_pro_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Gemini Pro Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gemini_pro_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Gemini Pro Model') }} {{ number_format($subscription->gemini_pro_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if ($subscription->dalle_image_engine != 'none')
                                                                            @if ($subscription->dalle_images == -1)
                                                                                <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Dalle images') }}</span></li>
                                                                            @else
                                                                                @if($subscription->dalle_images != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->dalle_images) }}</span> <span class="plan-feature-text">{{ __('Dalle images') }}</span></li> @endif
                                                                            @endif
                                                                        @endif																
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if ($subscription->sd_image_engine != 'none')
                                                                            @if ($subscription->sd_images == -1)
                                                                                <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('SD images') }}</span></li>
                                                                            @else
                                                                                @if($subscription->sd_images != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->sd_images) }}</span> <span class="plan-feature-text">{{ __('SD images') }}</span></li> @endif
                                                                            @endif
                                                                        @endif																	
                                                                    @endif
                                                                    @if (config('settings.whisper_feature_user') == 'allow')
                                                                        @if ($subscription->minutes == -1)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('minutes') }}</span></li>
                                                                        @else
                                                                            @if($subscription->minutes != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->minutes) }}</span> <span class="plan-feature-text">{{ __('minutes') }}</span></li> @endif
                                                                        @endif																	
                                                                    @endif
                                                                    @if (config('settings.voiceover_feature_user') == 'allow')
                                                                        @if ($subscription->characters == -1)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('characters') }}</span></li>
                                                                        @else
                                                                            @if($subscription->characters != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->characters) }}</span> <span class="plan-feature-text">{{ __('characters') }}</span></li> @endif
                                                                        @endif																	
                                                                    @endif
                                                                        @if($subscription->team_members != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->team_members) }}</span> <span class="plan-feature-text">{{ __('team members') }}</span></li> @endif
                                                                    
                                                                    @if (config('settings.writer_feature_user') == 'allow')
                                                                        @if($subscription->writer_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Writer Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.wizard_feature_user') == 'allow')
                                                                        @if($subscription->wizard_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Article Wizard Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.smart_editor_feature_user') == 'allow')
                                                                        @if($subscription->smart_editor_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Smart Editor Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.rewriter_feature_user') == 'allow')
                                                                        @if($subscription->rewriter_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI ReWriter Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_feature_user') == 'allow')
                                                                        @if($subscription->chat_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Chats Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if($subscription->image_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Images Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.voiceover_feature_user') == 'allow')
                                                                        @if($subscription->voiceover_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Voiceover Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.video_feature_user') == 'allow')
                                                                        @if($subscription->video_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Video Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.voice_clone_feature_user') == 'allow')
                                                                        @if($subscription->voice_clone_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Voice Clone Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.sound_studio_feature_user') == 'allow')
                                                                        @if($subscription->sound_studio_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Sound Studio Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.whisper_feature_user') == 'allow')
                                                                        @if($subscription->transcribe_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Speech to Text Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.plagiarism_checker_feature_user') == 'allow')
                                                                        @if($subscription->plagiarism_checker_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Plagiarism Checker Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.vision_feature_user') == 'allow')
                                                                        @if($subscription->vision_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Vision Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.ai_detector_feature_user') == 'allow')
                                                                        @if($subscription->ai_detector_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Detector Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_file_feature_user') == 'allow')
                                                                        @if($subscription->chat_file_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI File Chat Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_web_feature_user') == 'allow')
                                                                        @if($subscription->chat_web_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Web Chat Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.code_feature_user') == 'allow')
                                                                        @if($subscription->code_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Code Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if($subscription->team_members) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Team Members Option') }}</span></li> @endif
                                                                    @foreach ( (explode(',', $subscription->plan_features)) as $feature )
                                                                        @if ($feature)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ $feature }}</li>
                                                                        @endif																
                                                                    @endforeach															
                                                                </ul>																
                                                            </div>					
                                                        </div>	
                                                    </div>							
                                                </div>											
                                            @endforeach
    
                                        </div>	
                                    
                                    @else
                                        <div class="row text-center">
                                            <div class="col-sm-12 mt-6 mb-6">
                                                <h6 class="fs-12 font-weight-bold text-center">{{ __('No Subscriptions plans were set yet') }}</h6>
                                            </div>
                                        </div>
                                    @endif					
                                </div>
                            @endif	
                            
                            @if ($lifetime)
                                <div class="tab-pane @if ((!$monthly && $lifetime) && (!$yearly && $lifetime)) active @else '' @endif" id="lifetime">

                                    @if ($lifetime_subscriptions->count())                                                    
                                        
                                        <div class="row justify-content-md-center">
                                        
                                            @foreach ( $lifetime_subscriptions as $subscription )																			
                                                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="200" data-aos-once="true" data-aos-duration="400">
                                                    <div class="pt-2 ml-2 mr-2 h-100 prices-responsive">
                                                        <div class="card p-5 mb-4 pl-6 pr-6 h-100 price-card @if ($subscription->featured) price-card-border @endif">
                                                            @if ($subscription->featured)
                                                                <span class="plan-featured">{{ __('Most Popular') }}</span>
                                                            @endif
                                                            <div class="plan">			
                                                                <div class="plan-title">{{ $subscription->plan_name }}</div>
                                                                <p class="plan-cost mb-5">
                                                                    @if ($subscription->free)
                                                                        {{ __('Free') }}
                                                                    @else
                                                                        {!! config('payment.default_system_currency_symbol') !!}@if(config('payment.decimal_points') == 'allow'){{ number_format((float)$subscription->price, 2) }} @else{{ number_format($subscription->price) }} @endif<span class="fs-12 text-muted"><span class="mr-1">/</span> {{ __('for lifetime') }}</span>
                                                                    @endif	
                                                                </p>																				
                                                                <div class="text-center action-button mt-2 mb-5">
                                                                    <a href="{{ route('user.prepaid.checkout', ['type' => 'lifetime', 'id' => $subscription->id]) }}" class="btn btn-primary-pricing">{{ __('Subscribe Now') }}</a>                                               														
                                                                </div>
                                                                <p class="fs-12 mb-3 text-muted">{{ __('Included Features') }}</p>																	
                                                                <ul class="fs-12 pl-3">	
                                                                    @if ($subscription->gpt_4_turbo_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4T Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4_turbo_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4T') }} {{ number_format($subscription->gpt_4_turbo_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4 Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4') }} {{ number_format($subscription->gpt_4_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4o_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4o Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4o_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4o') }} {{ number_format($subscription->gpt_4o_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_4o_mini_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 4o  miniModel') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_4o_mini_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 4o mini') }} {{ number_format($subscription->gpt_4o_mini_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gpt_3_turbo_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('GPT 3.5T Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gpt_3_turbo_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('GPT 3.5T') }} {{ number_format($subscription->gpt_3_turbo_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->fine_tune_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Fine Tune Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->fine_tune_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Fine Tune Model') }} {{ number_format($subscription->fine_tune_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_opus_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3 Opus Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_opus_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3 Opus Model') }} {{ number_format($subscription->claude_3_opus_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_sonnet_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3.5 Sonnet Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_sonnet_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3.5 Sonnet Model') }} {{ number_format($subscription->claude_3_sonnet_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->claude_3_haiku_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Claude 3 Haiku Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->claude_3_haiku_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Claude 3 Haiku Model') }} {{ number_format($subscription->claude_3_haiku_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if ($subscription->gemini_pro_credits == -1)
                                                                        <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Gemini Pro Model') }} {{ __('words') }}</span></li>
                                                                    @else	
                                                                        @if($subscription->gemini_pro_credits != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Gemini Pro Model') }} {{ number_format($subscription->gemini_pro_credits) }}</span> <span class="plan-feature-text">{{ __('words') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if ($subscription->dalle_image_engine != 'none')
                                                                            @if ($subscription->dalle_images == -1)
                                                                                <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('Dalle images') }}</span></li>
                                                                            @else
                                                                                @if($subscription->dalle_images != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->dalle_images) }}</span> <span class="plan-feature-text">{{ __('Dalle images') }}</span></li> @endif
                                                                            @endif
                                                                        @endif																
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if ($subscription->sd_image_engine != 'none')
                                                                            @if ($subscription->sd_images == -1)
                                                                                <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('SD images') }}</span></li>
                                                                            @else
                                                                                @if($subscription->sd_images != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->sd_images) }}</span> <span class="plan-feature-text">{{ __('SD images') }}</span></li> @endif
                                                                            @endif
                                                                        @endif																	
                                                                    @endif
                                                                    @if (config('settings.whisper_feature_user') == 'allow')
                                                                        @if ($subscription->minutes == -1)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('minutes') }}</span></li>
                                                                        @else
                                                                            @if($subscription->minutes != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->minutes) }}</span> <span class="plan-feature-text">{{ __('minutes') }}</span></li> @endif
                                                                        @endif																	
                                                                    @endif
                                                                    @if (config('settings.voiceover_feature_user') == 'allow')
                                                                        @if ($subscription->characters == -1)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ __('Unlimited') }}</span> <span class="plan-feature-text">{{ __('characters') }}</span></li>
                                                                        @else
                                                                            @if($subscription->characters != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->characters) }}</span> <span class="plan-feature-text">{{ __('characters') }}</span></li> @endif
                                                                        @endif																	
                                                                    @endif
                                                                        @if($subscription->team_members != 0) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="font-weight-bold">{{ number_format($subscription->team_members) }}</span> <span class="plan-feature-text">{{ __('team members') }}</span></li> @endif
                                                                    
                                                                    @if (config('settings.writer_feature_user') == 'allow')
                                                                        @if($subscription->writer_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Writer Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.wizard_feature_user') == 'allow')
                                                                        @if($subscription->wizard_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Article Wizard Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.smart_editor_feature_user') == 'allow')
                                                                        @if($subscription->smart_editor_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Smart Editor Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.rewriter_feature_user') == 'allow')
                                                                        @if($subscription->rewriter_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI ReWriter Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_feature_user') == 'allow')
                                                                        @if($subscription->chat_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Chats Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.image_feature_user') == 'allow')
                                                                        @if($subscription->image_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Images Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.voiceover_feature_user') == 'allow')
                                                                        @if($subscription->voiceover_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Voiceover Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.video_feature_user') == 'allow')
                                                                        @if($subscription->video_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Video Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.voice_clone_feature_user') == 'allow')
                                                                        @if($subscription->voice_clone_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Voice Clone Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.sound_studio_feature_user') == 'allow')
                                                                        @if($subscription->sound_studio_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Sound Studio Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.whisper_feature_user') == 'allow')
                                                                        @if($subscription->transcribe_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Speech to Text Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.plagiarism_checker_feature_user') == 'allow')
                                                                        @if($subscription->plagiarism_checker_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Plagiarism Checker Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.vision_feature_user') == 'allow')
                                                                        @if($subscription->vision_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Vision Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.ai_detector_feature_user') == 'allow')
                                                                        @if($subscription->ai_detector_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Detector Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_file_feature_user') == 'allow')
                                                                        @if($subscription->chat_file_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI File Chat Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.chat_web_feature_user') == 'allow')
                                                                        @if($subscription->chat_web_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Web Chat Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if (config('settings.code_feature_user') == 'allow')
                                                                        @if($subscription->code_feature) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('AI Code Feature') }}</span></li> @endif
                                                                    @endif
                                                                    @if($subscription->team_members) <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> <span class="plan-feature-text">{{ __('Team Members Option') }}</span></li> @endif
                                                                    @foreach ( (explode(',', $subscription->plan_features)) as $feature )
                                                                        @if ($feature)
                                                                            <li class="fs-13 mb-3"><i class="fa-solid fa-check fs-14 mr-2 text-success"></i> {{ $feature }}</li>
                                                                        @endif																
                                                                    @endforeach															
                                                                </ul>																
                                                            </div>					
                                                        </div>	
                                                    </div>							
                                                </div>											
                                            @endforeach					

                                        </div>

                                    @else
                                        <div class="row text-center">
                                            <div class="col-sm-12 mt-6 mb-6">
                                                <h6 class="fs-12 font-weight-bold text-center">{{ __('No lifetime plans were set yet') }}</h6>
                                            </div>
                                        </div>
                                    @endif

                                </div>	
                            @endif	
                        </div>
                    </div>
                
                @else
                    <div class="row text-center">
                        <div class="col-sm-12 mt-6 mb-6">
                            <h6 class="fs-12 font-weight-bold text-center">{{ __('No Subscriptions or Prepaid plans were set yet') }}</h6>
                        </div>
                    </div>
                @endif

                <div class="text-center">
                    <p class="mb-0 mt-2"><i class="fa-solid fa-shield-check text-success mr-2"></i><span class="text-muted fs-12">{{ __('PCI DSS Compliant') }}</span></p>
                </div>
    
            </div>
        </div>
    
    </div>

</section>
  