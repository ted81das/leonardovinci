<!-- SIDE MENU BAR -->
<aside class="app-sidebar"> 
    <div class="app-sidebar__logo">
        <a class="header-brand" href="{{url('/')}}">
            <img src="{{ URL::asset($settings->logo_dashboard)}}" class="header-brand-img desktop-lgo" alt="Dashboard Logo">
                <img src="{{ URL::asset($settings->logo_dashboard_collapsed)}}" class="header-brand-img mobile-logo" alt="Dashboard Logo">
        </a>
    </div>
    <ul class="side-menu app-sidebar3">
        <li class="side-item side-item-category mt-4 mb-3">{{ __('AI Panel') }}</li>
        <li class="slide">
            <a class="side-menu__item" href="{{ route('user.dashboard') }}">
            <span class="side-menu__icon lead-3 fa-solid fa-chart-tree-map"></span>
            <span class="side-menu__label">{{ __('Dashboard') }}</span></a>
        </li> 
        @if (config('settings.writer_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.templates') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-microchip-ai"></span>
                <span class="side-menu__label">{{ __('AI Writer') }}</span></a>
            </li>
        @endif 
        @if (config('settings.wizard_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.wizard') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-sharp fa-sparkles"></span>
                <span class="side-menu__label">{{ __('AI Article Wizard') }}</span></a>
            </li> 
        @endif
        @if (config('settings.smart_editor_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.smart.editor') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-feather"></span>
                <span class="side-menu__label">{{ __('Smart Editor') }}</span></a>
            </li> 
        @endif
        @if (config('settings.rewriter_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.rewriter') }}">
                <span class="side-menu__icon lead-3 fs-17 fa-solid fa-pen-line"></span>
                <span class="side-menu__label">{{ __('AI ReWriter') }}</span></a>
            </li> 
        @endif
        @if (config('settings.plagiarism_checker_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.plagiarism') }}">
                <span class="side-menu__icon fa-sharp fa-solid fa-shield-check"></span>
                <span class="side-menu__label">{{ __('AI Plagiarism Checker') }}</span></a>
            </li> 
        @endif
        @if (config('settings.ai_detector_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.detector') }}">
                <span class="side-menu__icon fa-sharp fa-solid fa-user-secret"></span>
                <span class="side-menu__label">{{ __('AI Content Detector') }}</span></a>
            </li> 
        @endif
        @if (config('settings.video_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#') }}">
                    <span class="side-menu__icon lead-3 fs-17 fa-solid fa-video"></span>
                    <span class="side-menu__label">{{ __('AI Video') }}</span><i class="angle fa fa-angle-right"></i>
                </a>
                <ul class="slide-menu">
                    <li><a href="{{ route('user.video') }}" class="slide-item">{{ __('Image to Video') }}</a></li>
                </ul>
            </li> 
        @endif
        @if (config('settings.image_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.images') }}">
                <span class="side-menu__icon lead-3 fa-solid fa-camera-viewfinder"></span>
                <span class="side-menu__label">{{ __('AI Images') }}</span></a>
            </li> 
        @endif
        @if (config('settings.photo_studio_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.photo.studio') }}">
                <span class="side-menu__icon lead-3 fa-solid fa-photo-film"></span>
                <span class="side-menu__label">{{ __('AI Photo Studio') }}</span></a>
            </li> 
        @endif
        @if (config('settings.voiceover_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#') }}">
                    <span class="side-menu__icon fa-sharp fa-solid fa-waveform-lines"></span>
                    <span class="side-menu__label">{{ __('AI Voiceover') }}</span><i class="angle fa fa-angle-right"></i>
                </a>
                <ul class="slide-menu">
                    <li><a href="{{ route('user.voiceover') }}" class="slide-item">{{ __('Text to Speech') }}</a></li>
                    @if (config('settings.voice_clone_feature_user') == 'allow')
                        <li><a href="{{ route('user.voiceover.clone') }}" class="slide-item">{{ __('Voice Cloning') }}</a></li>
                    @endif
                    @if (config('settings.sound_studio_feature_user') == 'allow')
                        <li><a href="{{ route('user.studio') }}" class="slide-item">{{ __('Sound Studio') }}</a></li>
                    @endif
                </ul>
            </li> 
        @endif
        @if (config('settings.whisper_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.transcribe') }}">
                <span class="side-menu__icon fa-sharp fa-solid fa-folder-music"></span>
                <span class="side-menu__label">{{ __('AI Speech to Text') }}</span></a>
            </li> 
        @endif
        @if (config('settings.chat_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.chat') }}">
                <span class="side-menu__icon lead-3 fa-solid fa-message-captions"></span>
                <span class="side-menu__label">{{ __('AI Chat') }}</span></a>
            </li> 
        @endif
        @if (config('settings.vision_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.vision') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-brain-circuit"></span>
                <span class="side-menu__label">{{ __('AI Vision') }}</span></a>
            </li> 
        @endif
        @if (config('settings.chat_file_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.chat.file') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-folder-grid"></span>
                <span class="side-menu__label">{{ __('AI File Chat') }}</span></a>
            </li> 
        @endif
        @if (config('settings.chat_web_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.chat.web') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-globe"></span>
                <span class="side-menu__label">{{ __('AI Web Chat') }}</span></a>
            </li> 
        @endif
        @if (App\Services\HelperService::checkYoutubeFeature())
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.youtube') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-brands fa-youtube"></span>
                <span class="side-menu__label">{{ __('AI Youtube') }}</span></a>
            </li> 
        @endif
        @if (App\Services\HelperService::checkRSSFeature())
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.rss') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-rss"></span>
                <span class="side-menu__label">{{ __('AI RSS') }}</span></a>
            </li> 
        @endif
        @if (config('settings.chat_image_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.chat.image') }}">
                <span class="side-menu__icon lead-3 fs-18 fa-solid fa-image-landscape"></span>
                <span class="side-menu__label">{{ __('AI Chat Image') }}</span></a>
            </li> 
        @endif
        @if (config('settings.code_feature_user') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.codex') }}">
                <span class="side-menu__icon fa-solid fa-square-code"></span>
                <span class="side-menu__label">{{ __('AI Code') }}</span></a>
            </li> 
        @endif 
        @if (App\Services\HelperService::checkBrandsFeature())
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.brand') }}">
                <span class="side-menu__icon fa-solid fa-signature"></span>
                <span class="side-menu__label">{{ __('Brand Voice') }}</span></a>
            </li> 
        @endif 
        @if (config('settings.integration_feature_user') == 'allow')
            @if (App\Services\HelperService::checkIntegrationFeature())
                <li class="slide">
                    <a class="side-menu__item" href="{{ route('user.integration') }}">
                    <span class="side-menu__icon fa-solid fa-rectangles-mixed"></span>
                    <span class="side-menu__label">{{ __('Integrations') }}</span></a>
                </li> 
            @endif 
        @endif
        <li class="slide mb-3">
            <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                <span class="side-menu__icon fa-solid fa-folder-bookmark"></span>
                <span class="side-menu__label">{{ __('Documents') }}</span><i class="angle fa fa-angle-right"></i></a>
                <ul class="slide-menu">
                    <li><a href="{{ route('user.documents') }}" class="slide-item">{{ __('All Documents') }}</a></li>
                    @if (config('settings.image_feature_user') == 'allow')
                        <li><a href="{{ route('user.documents.images') }}" class="slide-item">{{ __('All Images') }}</a></li> 
                    @endif 
                    @if (config('settings.voiceover_feature_user') == 'allow')
                        <li><a href="{{ route('user.documents.voiceovers') }}" class="slide-item">{{ __('All Voiceovers') }}</a></li> 
                    @endif 
                    @if (config('settings.whisper_feature_user') == 'allow')
                        <li><a href="{{ route('user.documents.transcripts') }}" class="slide-item">{{ __('All Transcripts') }}</a></li> 
                    @endif 
                    @if (config('settings.code_feature_user') == 'allow')
                        <li><a href="{{ route('user.documents.codes') }}" class="slide-item">{{ __('All Codes') }}</a></li> 
                    @endif 
                    <li><a href="{{ route('user.workbooks') }}" class="slide-item">{{ __('Workbooks') }}</a></li>                    
                </ul>
        </li>       
        <hr class="w-90 text-center m-auto">
        <li class="side-item side-item-category mt-4 mb-3">{{ __('Account') }}</li>
        <li class="slide">
            <a class="side-menu__item" href="{{ route('user.plans') }}">
            <span class="side-menu__icon lead-3 fa-solid fa-box-circle-check"></span>
            <span class="side-menu__label">{{ __('Subscription Plans') }}</span></a>
        </li>
        @if (config('settings.team_members_feature') == 'allow')
            <li class="slide">
                <a class="side-menu__item" href="{{ route('user.team') }}">
                <span class="side-menu__icon lead-3 fa-solid fa-people-arrows"></span>
                <span class="side-menu__label">{{ __('Team Members') }}</span></a>
            </li>
        @endif 
        <li class="slide">
            <a class="side-menu__item" href="{{ route('user.profile') }}">
            <span class="side-menu__icon lead-3 fa-solid fa-id-badge"></span>
            <span class="side-menu__label">{{ __('My Account') }}</span></a>
        </li>
        @if (config('payment.referral.enabled') == 'on')
            <li class="slide mb-3">
                <a class="side-menu__item" href="{{ route('user.referral') }}">
                <span class="side-menu__icon lead-3 fa-solid fa-badge-dollar"></span>
                <span class="side-menu__label">{{ __('Affiliate Program') }}</span></a>
            </li>
        @endif 
        @role('admin')
            <hr class="w-90 text-center m-auto">
            <li class="side-item side-item-category mt-4 mb-3">{{ __('Admin Panel') }}</li>
            <li class="slide">
                <a class="side-menu__item" href="{{ route('admin.themes') }}">
                <span class="side-menu__icon lead-3 fa-solid fa-palette"></span>
                <span class="side-menu__label">{{ __('Themes') }}</span></a>
            </li>
            <li class="slide">
                <a class="side-menu__item"  href="{{ route('admin.dashboard') }}">
                    <span class="side-menu__icon fa-solid fa-chart-tree-map"></span>
                    <span class="side-menu__label">{{ __('Dashboard') }}</span>
                </a>
            </li>
            <li class="slide">
                    <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                        <span class="side-menu__icon fa-solid fa-microchip-ai fs-18"></span>
                        <span class="side-menu__label">{{ __('AI Management') }}</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.davinci.dashboard') }}" class="slide-item">{{ __('AI Usage Dashboard') }}</a></li>                        
                        <li><a href="{{ route('admin.davinci.image.prompt') }}" class="slide-item">{{ __('AI Image Prompts') }}</a></li>
                        <li><a href="{{ route('admin.davinci.voices') }}" class="slide-item">{{ __('AI Voiceover Voices') }}</a></li>                        
                        <li><a href="{{ route('admin.davinci.configs') }}" class="slide-item">{{ __('AI Settings') }}</a></li>
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa-solid fa-feather fs-18"></span>
                    <span class="side-menu__label">{{ __('Template Settings') }}</span><i class="angle fa fa-angle-right"></i>
                </a>
                <ul class="slide-menu">
                    <li><a href="{{ route('admin.davinci.custom.category') }}" class="slide-item">{{ __('Template Categories') }}</a></li>
                    <li><a href="{{ route('admin.davinci.templates') }}" class="slide-item">{{ __('Original Templates') }}</a></li>
                    <li><a href="{{ route('admin.davinci.custom') }}" class="slide-item">{{ __('Custom Templates') }}</a></li>                    
                </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa-solid fa-brain-circuit fs-18"></span>
                    <span class="side-menu__label">{{ __('Chat Settings') }}</span><i class="angle fa fa-angle-right"></i>
                </a>
                <ul class="slide-menu">                                       
                    <li><a href="{{ route('admin.davinci.chat.category') }}" class="slide-item">{{ __('Chat Categories') }}</a></li>
                    <li><a href="{{ route('admin.davinci.chat.prompt') }}" class="slide-item">{{ __('Chat Prompts') }}</a></li>
                    <li><a href="{{ route('admin.davinci.chats') }}" class="slide-item">{{ __('Original Chatbots') }}</a></li>                   
                    <li><a href="{{ route('admin.chat.assistant') }}" class="slide-item">{{ __('Chat Assistants') }}</a></li>                   
                    {{-- <li><a href="{{ route('admin.chat.training') }}" class="slide-item">{{ __('Chat Training') }}</a></li>                    --}}
                </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa-solid fa-id-badge"></span>
                    <span class="side-menu__label">{{ __('User Management') }}</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.user.dashboard') }}" class="slide-item">{{ __('User Dashboard') }}</a></li>
                        <li><a href="{{ route('admin.user.list') }}" class="slide-item">{{ __('User List') }}</a></li>
                        <li><a href="{{ route('admin.user.activity') }}" class="slide-item">{{ __('Activity Monitoring') }}</a></li>
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa-solid fa-sack-dollar"></span>
                    <span class="side-menu__label">{{ __('Finance Management') }}</span>
                    @if (auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count())
                        <span class="badge badge-warning">{{ auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count() }}</span>
                    @else
                        <i class="angle fa fa-angle-right"></i>
                    @endif
                </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.finance.dashboard') }}" class="slide-item">{{ __('Finance Dashboard') }}</a></li>
                        <li><a href="{{ route('admin.finance.transactions') }}" class="slide-item">{{ __('Transactions') }}</a></li>
                        <li><a href="{{ route('admin.finance.plans') }}" class="slide-item">{{ __('Subscription Plans') }}</a></li>
                        <li><a href="{{ route('admin.finance.prepaid') }}" class="slide-item">{{ __('Prepaid Plans') }}</a></li>
                        <li><a href="{{ route('admin.finance.subscriptions') }}" class="slide-item">{{ __('Subscribers') }}</a></li>
                        <li><a href="{{ route('admin.finance.promocodes') }}" class="slide-item">{{ __('Promocodes') }}</a></li>
                        <li><a href="{{ route('admin.referral.settings') }}" class="slide-item">{{ __('Referral System') }}</a></li>
                        <li><a href="{{ route('admin.referral.payouts') }}" class="slide-item">{{ __('Referral Payouts') }}
                                @if ((auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count()))
                                    <span class="badge badge-warning ml-5">{{ auth()->user()->unreadNotifications->where('type', 'App\Notifications\PayoutRequestNotification')->count() }}</span>
                                @endif
                            </a>
                        </li>
                        <li><a href="{{ route('admin.settings.invoice') }}" class="slide-item">{{ __('Invoice Settings') }}</a></li>
                        <li><a href="{{ route('admin.finance.settings') }}" class="slide-item">{{ __('Payment Settings') }}</a></li>
                    </ul>
            </li>
            @if (config('settings.user_support') == 'enabled')
                <li class="slide">
                    <a class="side-menu__item"  href="{{ route('admin.support') }}">
                        <span class="side-menu__icon fa-solid fa-message-question"></span>
                        <span class="side-menu__label">{{ __('Support Requests') }}</span>
                        @if (App\Models\SupportTicket::where('status', 'Open')->count())
                            <span class="badge badge-warning">{{ App\Models\SupportTicket::where('status', 'Open')->count() }}</span>
                        @endif 
                    </a>
                </li>
            @endif
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa-solid fa-envelope-circle-check"></span>
                    <span class="side-menu__label">{{ __('Email & Notifications') }}</span>
                        @if (auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count())
                            <span class="badge badge-warning" id="total-notifications-a"></span>
                        @else
                            <i class="angle fa fa-angle-right"></i>
                        @endif
                    </a>                     
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.email.newsletter') }}" class="slide-item">{{ __('Newsletter') }}</a></li>
                        <li><a href="{{ route('admin.email.templates') }}" class="slide-item">{{ __('Email Templates') }}</a></li>
                        <li><a href="{{ route('admin.notifications') }}" class="slide-item">{{ __('Mass Notifications') }}</a></li>
                        <li><a href="{{ route('admin.notifications.system') }}" class="slide-item">{{ __('System Notifications') }} 
                                @if ((auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count()))
                                    <span class="badge badge-warning ml-5" id="total-notifications-b"></span>
                                @endif
                            </a>
                        </li> 
                    </ul>
            </li>
            <li class="slide">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa-solid fa-earth-americas"></span>
                    <span class="side-menu__label">{{ __('Frontend Management') }}</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.settings.frontend') }}" class="slide-item">{{ __('Frontend Settings') }}</a></li>
                        <li><a href="{{ route('admin.settings.section') }}" class="slide-item">{{ __('Frontend Sections') }}</a></li>
                        <li><a href="{{ route('admin.settings.seo') }}" class="slide-item">{{ __('SEO Manager') }}</a></li>
                        <li><a href="{{ route('admin.settings.page') }}" class="slide-item">{{ __('Pages') }}</a></li>
                        <li><a href="{{ route('admin.settings.appearance') }}" class="slide-item">{{ __('Logos') }}</a></li>
                        <li><a href="{{ route('admin.settings.step') }}" class="slide-item">{{ __('How it Works Section') }}</a></li>
                        <li><a href="{{ route('admin.settings.case') }}" class="slide-item">{{ __('Use Cases Section') }}</a></li>                                           
                        <li><a href="{{ route('admin.settings.tool') }}" class="slide-item">{{ __('AI Tools Section') }}</a></li>                                           
                        <li><a href="{{ route('admin.settings.feature') }}" class="slide-item">{{ __('Features Section') }}</a></li>                      
                        <li><a href="{{ route('admin.settings.client') }}" class="slide-item">{{ __('Clients Section') }}</a></li>                      
                        <li><a href="{{ route('admin.settings.review') }}" class="slide-item">{{ __('Reviews Manager') }}</a></li>                      
                        <li><a href="{{ route('admin.settings.blog') }}" class="slide-item">{{ __('Blogs Manager') }}</a></li>
                        <li><a href="{{ route('admin.settings.faq') }}" class="slide-item">{{ __('FAQs Manager') }}</a></li>                                                 
                        <li><a href="{{ route('admin.settings.adsense') }}" class="slide-item">{{ __('Google Adsense') }}</a></li>                           
                    </ul>
            </li>
            <li class="slide mb-3">
                <a class="side-menu__item" data-toggle="slide" href="{{ url('#')}}">
                    <span class="side-menu__icon fa fa-sliders"></span>
                    <span class="side-menu__label">{{ __('General Settings') }}</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.settings.global') }}" class="slide-item">{{ __('Global Settings') }}</a></li>
                        <li><a href="{{ route('admin.settings.oauth') }}" class="slide-item">{{ __('Auth Settings') }}</a></li>
                        <li><a href="{{ route('admin.settings.registration') }}" class="slide-item">{{ __('Registration Settings') }}</a></li>
                        <li><a href="{{ route('admin.settings.smtp') }}" class="slide-item">{{ __('SMTP Settings') }}</a></li>
                        <li><a href="{{ route('elseyyid.translations.home2') }}" class="slide-item">{{ __('Languages') }}</a></li>     
                        <li><a href="{{ route('admin.settings.activation') }}" class="slide-item">{{ __('Activation') }}</a></li>     
                        <li><a href="{{ route('admin.settings.upgrade') }}" class="slide-item">{{ __('Upgrade Software') }}</a></li>                 
                        <li><a href="{{ route('admin.settings.clear') }}" class="slide-item">{{ __('Clear Cache') }}</a></li>                 
                    </ul>
            </li>
        @endrole
        <hr class="w-90 text-center m-auto">
        <div class="side-progress-position mt-4">
            <div class="side-plan-wrapper text-center pt-3 pb-3">
                <span class="side-item side-item-category mt-4">{{ __('Plan') }}: @if (is_null(auth()->user()->plan_id))<span class="text-primary">{{ __('No Active Subscription') }}</span> @else <span class="text-primary">{{ __(App\Services\HelperService::getPlanName())}}</span>  @endif </span>
                <div class="view-credits mt-1"><a class=" fs-11 text-muted mb-2" href="javascript:void(0)" id="view-credits" data-bs-toggle="modal" data-bs-target="#creditsModel"><i class="fa-solid fa-coin-front text-yellow "></i> {{ __('View Credits') }}</a></div> 
                @if (is_null(auth()->user()->plan_id))
                    <div class="text-center mt-3 mb-2"><a href="{{ route('user.plans') }}" class="btn btn-primary pl-6 pr-6 fs-11"> <i class="fa-solid fa-bolt text-yellow mr-2"></i> {{ __('Upgrade') }}</a></div> 
                @endif              
            </div>
            @if (config('payment.referral.enabled') == 'on')
                <div class="side-plan-wrapper mt-4 text-center p-3 pl-5 pr-5">
                    <div class="mb-1"><i class="fa-solid fa-gifts fs-20 text-yellow"></i></div>
                    <span class="fs-12 mt-4" style="color: #344050">{{ __('Invite your friends and get') }} {{ config('payment.referral.payment.commission') }}% @if (config('payment.referral.payment.policy') == 'all') {{ __('of all their purchases') }} @else {{ __('of their first purchase') }}@endif</span>
                    <div class="text-center mt-3 mb-2"><a href="{{ route('user.referral') }}" class="btn btn-primary pl-6 pr-6 fs-11" id="referral-button"> {{ __('Invite Friends') }}</a></div>              
                </div>
            @endif
        </div>
    </ul>
</aside>

<div class="modal fade" id="creditsModel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
          <div class="modal-content">
            <div class="modal-header">
                <h6 class="text-center font-weight-bold fs-16"> {{ __('Credits on') }} {{ config('app.name') }}</h6>	
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pl-5 pr-5">
                
                <h6 class="font-weight-semibold mb-2 mt-3">{{ __('Unlock your creativity with') }} {{ config('app.name') }} {{ __('credits') }}</h6>
                <p class="text-muted">{{ __('Maximize your content creation with') }} {{ config('app.name') }}. {{ __('Each credit unlocks powerful AI tools and features designed to enhance your content creation.') }}</p>
                
                <div class="d-flex justify-content-between mt-3">
                    <div class="font-weight-bold fs-12">{{ __('AI Chats/Templates') }}</div>
                    <div class="font-weight-bold fs-12">{{ __('Credits') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('OpenAI GPT 4o') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableGPT4oWords() == -1) {{ __('Unlimited') }} @else {{ \App\Services\HelperService::userAvailableGPT4oWords()}}  @endif{{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('OpenAI GPT 4o Mini') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableGPT4oMiniWords() == -1) {{ __('Unlimited') }} @else {{ \App\Services\HelperService::userAvailableGPT4oMiniWords()}}  @endif{{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('OpenAI GPT 4') }}</div>
                    <div class="text-muted fs-10"><span>@if (\App\Services\HelperService::userAvailableGPT4Words() == -1) {{ __('Unlimited') }} @else {{ \App\Services\HelperService::userAvailableGPT4Words()}} @endif {{ __('Words') }}</span></div>
                </div>                
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('OpenAI GPT 4 Turbo') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableGPT4TWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableGPT4TWords()}} @endif {{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('OpenAI GPT 3.5 Turbo') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableWords()}} @endif {{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('OpenAI Fine Tune') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableFineTuneWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableFineTuneWords()}} @endif {{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('Anthropic Claude 3 Opus') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableClaudeOpusWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableClaudeOpusWords()}} @endif {{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('Anthropic Claude 3.5 Sonnet') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableClaudeSonnetWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableClaudeSonnetWords()}} @endif {{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('Anthropic Claude 3 Haiku') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableClaudeHaikuWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableClaudeHaikuWords()}} @endif {{ __('Words') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('Google Gemini Pro') }}</div>
                    <div class="text-muted fs-10">@if (\App\Services\HelperService::userAvailableGeminiProWords() == -1) {{ __('Unlimited') }} @else {{ App\Services\HelperService::userAvailableGeminiProWords()}} @endif {{ __('Words') }}</div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <div class="font-weight-bold fs-12">{{ __('AI Image') }}</div>
                    <div class="font-weight-bold fs-12">{{ __('Credits') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('Dalle') }}</div>
                    <div class="text-muted fs-10">{{ \App\Services\HelperService::userAvailableDEImages()}} {{ __('Images') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('Stable Diffusion') }}</div>
                    <div class="text-muted fs-10">{{ \App\Services\HelperService::userAvailableSDImages()}} {{ __('Images') }}</div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <div class="font-weight-bold fs-12">{{ __('Extra') }}</div>
                    <div class="font-weight-bold fs-12">{{ __('Credits') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('AI Voiceover') }}</div>
                    <div class="text-muted fs-10">{{ App\Services\HelperService::getTotalCharacters()}} {{ __('Characters') }}</div>
                </div>
                <hr class="mt-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div class="text-muted fs-10">{{ __('AI Speech to Text') }}</div>
                    <div class="text-muted fs-10">{{ App\Services\HelperService::getTotalMinutes()}} {{ __('Minutes') }}</div>
                </div>
               
                <div class="text-center mt-4"><a href="{{ route('user.plans') }}" class="btn btn-primary pl-6 pr-6 fs-11" style="text-transform: none"> <i class="fa-solid fa-bolt text-yellow mr-2"></i> {{ __('Upgrade Now') }}</a></div> 
            </div>
          </div>
    </div>
</div>
<!-- END SIDE MENU BAR -->