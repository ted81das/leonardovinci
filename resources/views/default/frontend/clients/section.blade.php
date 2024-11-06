<section id="banner-wrapper">

    <div class="container">

        <!-- SECTION TITLE -->
        <div class="mb-8 text-center">

            <h6>{{ __($frontend_sections->clients_title) }} <span style="font-weight: 800; color: #0F2358">{{ __($frontend_sections->clients_title_dark) }}</span></h6>

        </div> <!-- END SECTION TITLE -->

        <div class="row" id="partners">

            @if ($client_exists)                          

                @foreach ( $clients as $client )
                    <div class="partner" data-aos="flip-down" data-aos-delay="{{  (200 * $client->id)/2 }}" data-aos-once="true" data-aos-duration="400">					
                        <div class="partner-image d-flex">
                            <div>
                                <img src="{{ URL::asset($client->url) }}" alt="partner">
                            </div>
                        </div>	
                    </div>
                @endforeach
            
            @endif
                    
        </div>
    </div>

</section>