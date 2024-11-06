<section id="blogs">

    <div class="container pt-9 text-center">

        <!-- SECTION TITLE -->
        <div class="row mb-7">
            <div class="title">
                <p class="m-2">{{ __($frontend_sections->blogs_subtitle) }}</p>
                <h3>{!! __($frontend_sections->blogs_title) !!}</h3>                        
            </div>
        </div> <!-- END SECTION TITLE --> 
                        
    </div> <!-- END CONTAINER -->

    <div class="container">

        @if ($blog_exists)
            
            <!-- BLOGS -->
            <div class="row blogs">
                @foreach ( $blogs as $blog )
                <div class="blog" data-aos="zoom-in" data-aos-delay="100" data-aos-once="true" data-aos-duration="400">			
                    <div class="blog-box">
                        <div class="blog-img">
                            <a href="{{ route('blogs.show', $blog->url) }}"><img src="{{ theme_url($blog->image) }}" alt="Blog Image"></a>
                            <span>{{ $blog->created_by }}</span>
                        </div>
                        <div class="blog-info mt-0">
                            <h6 class="blog-date text-left mt-1 mb-4">{{ date('F j, Y', strtotime($blog->created_at)) }}</h6>
                            <h5 class="blog-title fs-20 text-left mb-4"><a href="{{ route('blogs.show', $blog->url) }}">{{ __($blog->title) }}</a></h5>  
                            <h6><a href="{{ route('blogs.show', $blog->url) }}">{{ __('Read More') }}</a> <i class="fa-solid fa-chevrons-right"></i></h6>                                   
                        </div>
                    </div>                        
                </div> 
                @endforeach
            </div> 
            

            <!-- ROTATORS BUTTONS -->
            <div class="blogs-nav">
                <a class="blogs-prev"><i class="fa fa-chevron-left"></i></a>
                <a class="blogs-next"><i class="fa fa-chevron-right"></i></a>                                
            </div>

        @else
            <div class="row text-center">
                <div class="col-sm-12 mt-6 mb-6">
                    <h6 class="fs-12 font-weight-bold text-center">{{ __('No blog articles were published yet') }}</h6>
                </div>
            </div>
        @endif

        <div class="text-center blog-all mt-6">
            <a href="#">{{ __('Show More') }} <i class="fa-solid fa-chevrons-right fs-10"></i></a>
        </div>

    </div> <!-- END CONTAINER -->

    {!! adsense_frontend_blogs_728x90() !!}
    
</section> 
        