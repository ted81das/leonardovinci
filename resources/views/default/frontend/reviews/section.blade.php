<section id="reviews-wrapper">

    <div class="container text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-7">
            <div class="title">
                <p class="m-2 white">{{ __($frontend_sections->reviews_subtitle) }}</p>
                <h3 class="white">{!! __($frontend_sections->reviews_title) !!}</h3>                        
            </div>
        </div> <!-- END SECTION TITLE --> 
                    
    </div> <!-- END CONTAINER -->

    <div class="container">

        @if ($review_exists)
            <div class="reviews-card-wrapper">                               
                <div class="review-list-wrapper">
                    <div class="reviews-list">
                        
                        @foreach ($reviews as $review) 
                            @if ($review->row == 'first')
                                <div class="review-card-scroll w-inline-block">
                                    <div class="hori-between-div">
                                        <div class="horizontal-div mb-4">
                                            <img src="{{ theme_url($review->image_url) }}" loading="lazy" class="reviewer-portrait">
                                            <div>
                                                <div class="reviewer-name">{{ __($review->name) }}</div>
                                                <div class="reviewer-title">{{ __($review->position) }}</div>
                                            </div>                                                        
                                            <div class="reviewer-star">
                                                <span class="fs-11 mr-1">{{ $review->rating }}</span><i class="fa-solid fa-star fs-9 text-yellow"></i>
                                            </div>                                              
                                        </div>                                    
                                    </div>
                                    <p class="review-text"><i class="fa-solid fa-quote-left mr-2"></i>{{ __($review->text) }}<i class="fa-solid fa-quote-right ml-2"></i></p>
                                </div>
                            @endif                                        
                        @endforeach

                    </div>
                    <div class="reviews-list">
                        @foreach ($reviews as $review) 
                            @if ($review->row == 'first')
                                <div class="review-card-scroll w-inline-block">
                                    <div class="hori-between-div">
                                        <div class="horizontal-div mb-4">
                                            <img src="{{ theme_url($review->image_url) }}" loading="lazy" class="reviewer-portrait">
                                            <div>
                                                <div class="reviewer-name">{{ __($review->name) }}</div>
                                                <div class="reviewer-title">{{ __($review->position) }}</div>
                                            </div>
                                            <div class="reviewer-star">
                                                <span class="fs-11 mr-1">{{ $review->rating }}</span><i class="fa-solid fa-star fs-9 text-yellow"></i>
                                            </div> 
                                        </div>                                    
                                    </div>
                                    <p class="review-text"><i class="fa-solid fa-quote-left mr-2"></i>{{ __($review->text) }}<i class="fa-solid fa-quote-right ml-2"></i></p>
                                </div>
                            @endif                                        
                        @endforeach
                    </div>
                </div>

                @if ($review_second_exists)
                    <div class="review-list-wrapper second">
                        <div class="reviews-list">
                            @foreach ($reviews as $review) 
                                @if ($review->row == 'second')
                                    <div class="review-card-scroll w-inline-block">
                                        <div class="hori-between-div">
                                            <div class="horizontal-div mb-4">
                                                <img src="{{ theme_url($review->image_url) }}" loading="lazy" class="reviewer-portrait">
                                                <div>
                                                    <div class="reviewer-name">{{ __($review->name) }}</div>
                                                    <div class="reviewer-title">{{ __($review->position) }}</div>
                                                </div>
                                                <div class="reviewer-star">
                                                    <span class="fs-11 mr-1">{{ $review->rating }}</span><i class="fa-solid fa-star fs-9 text-yellow"></i>
                                                </div> 
                                            </div>                                    
                                        </div>
                                        <p class="review-text"><i class="fa-solid fa-quote-left mr-2"></i>{{ __($review->text) }}<i class="fa-solid fa-quote-right ml-2"></i></p>
                                    </div>
                                @endif                                        
                            @endforeach
                        </div>
                        <div class="reviews-list">
                            @foreach ($reviews as $review) 
                                @if ($review->row == 'second')
                                    <div class="review-card-scroll w-inline-block">
                                        <div class="hori-between-div">
                                            <div class="horizontal-div mb-4">
                                                <img src="{{ theme_url($review->image_url) }}" loading="lazy" class="reviewer-portrait">
                                                <div>
                                                    <div class="reviewer-name">{{ __($review->name) }}</div>
                                                    <div class="reviewer-title">{{ __($review->position) }}</div>
                                                </div>
                                                <div class="reviewer-star">
                                                    <span class="fs-11 mr-1">{{ $review->rating }}</span><i class="fa-solid fa-star fs-9 text-yellow"></i>
                                                </div> 
                                            </div>                                    
                                        </div>
                                        <p class="review-text"><i class="fa-solid fa-quote-left mr-2"></i>{{ __($review->text) }}<i class="fa-solid fa-quote-right ml-2"></i></p>
                                    </div>
                                @endif                                        
                            @endforeach
                        </div>
                    </div>
                @endif                           
                
            </div>
        @else
            <div class="row text-center">
                <div class="col-sm-12 mt-6 mb-6">
                    <h6 class="fs-12 font-weight-bold text-center">{{ __('No customer reviews were published yet') }}</h6>
                </div>
            </div>
        @endif

    </div>


</section> 
