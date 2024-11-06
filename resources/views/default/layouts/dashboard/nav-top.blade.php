<!-- TOP MENU BAR -->
<div class="app-header header">
    <div class="container-fluid"> 
        <div class="d-flex">
            <a class="header-brand" href="{{ url('/') }}">
                <img src="{{ URL::asset($settings->logo_dashboard)}}" class="header-brand-img desktop-lgo" alt="Dashboard Logo">
                <img src="{{ URL::asset($settings->logo_dashboard_collapsed)}}" class="header-brand-img mobile-logo" alt="Dashboard Logo">
            </a>
            <div class="app-sidebar__toggle nav-link icon" data-toggle="sidebar">
                <a class="open-toggle" href="{{url('#')}}">
                    <span class="fa fa-align-left header-icon"></span>
                </a>
            </div>
            <!-- SEARCH BAR -->
            <div id="search-bar">                
                <div>
                    <a class="nav-link icon">
                        <form id="search-field" action="{{ route('search') }}" method="POST" enctype="multipart/form-data">         
                            @csrf                   
                            <input type="search" name='keyword'>
                        </form>                        
                    </a>
                </div>                
            </div>
            <!-- END SEARCH BAR -->
            <!-- MENU BAR -->
            <div class="d-flex order-lg-2 ml-auto"> 
                <div id="form-group">
                    <select id="template-selection" name="template-selection" class="top-form-select" data-placeholder="{{ __('Create AI Document') }}" data-callback="changeTemplate">
                        @foreach (App\Services\HelperService::listTemplates() as $temp)
                            <option data-id="{{ $temp->template_code }}" value="app/user/templates/original-template/{{ $temp->slug }}" data-icon="{{   $temp->icon }}">{{ __($temp->name)  }}</option>
                        @endforeach	
                        @foreach (App\Services\HelperService::listCustomTemplates() as $temp)
                            <option data-id="{{ $temp->template_code }}" value="app/user/templates/{{ $temp->slug }}/{{ $temp->template_code }}" data-icon="{{   $temp->icon }}">{{ __($temp->name)  }}</option>
                        @endforeach																																
                    </select>
                </div>
                <div class="dropdown header-notify">
                    <a class="nav-link icon" data-bs-toggle="dropdown">                        
                        @role('admin')
                            <span class="header-icon fa-regular fa-bell pr-3"></span>
                            @if (auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count())
                                <span class="pulse "></span>
                            @endif
                        @endrole
                        @role('user|subscriber')
                            @if (config('settings.user_notification') == 'enabled')
                                <span class="header-icon fa-solid fa-bell pr-3"></span>                            
                                    @if (auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count())
                                        <span class="pulse "></span>
                                    @endif                            
                            @endif
                        @endrole
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow  animated">
                        @role('admin')
                            @if (auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification')->count())
                                <div class="dropdown-header">
                                    <h6 class="mb-0 fs-12 font-weight-bold notification-dark-theme"><span id="total-notifications"></span> <span class="text-primary">{{ __('New') }}</span> {{ __('Notification(s)') }}</h6>
                                    <a href="#" class="mb-1 badge badge-primary ml-auto pl-3 pr-3 mark-read" id="mark-all">{{ __('Mark All Read') }}</a>
                                </div>
                                <div class="notify-menu">
                                    <div class="notify-menu-inner">
                                        @foreach ( auth()->user()->unreadNotifications->where('type', '<>', 'App\Notifications\GeneralNotification') as $notification )
                                            <div class="d-flex dropdown-item border-bottom pl-4 pr-4">
                                                @if ($notification->data['type'] == 'new-user')                                                
                                                    <div>
                                                        <a href="{{ route('admin.notifications.systemShow', [$notification->id]) }}" class="d-flex">
                                                            <div class="notifyimg bg-info-transparent text-info"> <i class="fa-solid fa-user-check fs-18"></i></div>
                                                            <div class="mr-6">
                                                                <div class="font-weight-bold fs-12 notification-dark-theme">{{ __('New User Registered') }}</div>
                                                                <div class="text-muted fs-10">{{ __('Name') }}: {{ $notification->data['name'] }}</div>
                                                                <div class="small text-muted fs-10">{{ $notification->created_at->diffForHumans() }}</div>
                                                            </div>                                            
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <a href="#" class="badge badge-primary mark-read mark-as-read" data-id="{{ $notification->id }}">{{ __('Mark as Read') }}</a>
                                                    </div>
                                                @endif  
                                                @if ($notification->data['type'] == 'new-payment')                                                
                                                    <div>
                                                        <a href="{{ route('admin.notifications.systemShow', [$notification->id]) }}" class="d-flex">
                                                            <div class="notifyimg bg-info-green"> <i class="fa-solid fa-sack-dollar leading-loose"></i></div>
                                                            <div class="mr-4">
                                                                <div class="font-weight-bold fs-12 notification-dark-theme">{{ __('New User Payment') }}</div>
                                                                <div class="text-muted fs-10">{{ __('From') }}: {{ $notification->data['name'] }}</div>
                                                                <div class="small text-muted fs-10">{{ $notification->created_at->diffForHumans() }}</div>
                                                            </div>                                            
                                                        </a>
                                                    </div>
                                                    <div class="text-right">
                                                        <a href="#" class="badge badge-primary mark-read mark-as-read ml-5" data-id="{{ $notification->id }}">{{ __('Mark as Read') }}</a>
                                                    </div>
                                                @endif  
                                                @if ($notification->data['type'] == 'payout-request')                                                
                                                    <div>
                                                        <a href="{{ route('admin.notifications.systemShow', [$notification->id]) }}" class="d-flex">
                                                            <div class="notifyimg bg-info-green"> <i class="fa-solid fa-face-tongue-money fs-20 leading-loose"></i></div>
                                                            <div class="mr-4">
                                                                <div class="font-weight-bold fs-12 notification-dark-theme">{{ __('New Payout Request') }}</div>
                                                                <div class="text-muted fs-10">{{ __('From') }}: {{ $notification->data['name'] }}</div>
                                                                <div class="small text-muted fs-10">{{ $notification->created_at->diffForHumans() }}</div>
                                                            </div>                                            
                                                        </a>
                                                    </div>
                                                    <div class="text-right">
                                                        <a href="#" class="badge badge-primary mark-read mark-as-read ml-5" data-id="{{ $notification->id }}">{{ __('Mark as Read') }}</a>
                                                    </div>
                                                @endif                                                
                                            </div>
                                        @endforeach  
                                    </div>                              
                                </div>
                                <div class="view-all-button text-center">                            
                                    <a href="{{ route('admin.notifications.system') }}" class="fs-12 font-weight-bold notification-dark-theme">{{ __('View All Notifications') }}</a>
                                </div>                            
                            @else
                                <div class="view-all-button text-center">
                                    <h6 class=" fs-12 font-weight-bold mb-1 notification-dark-theme">{{ __('There are no new notifications') }}</h6>                                    
                                </div>
                            @endif
                        @endrole
                        @if (config('settings.user_notification') == 'enabled')
                            @role('user|subscriber')
                                @if (auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count())
                                    <div class="dropdown-header">
                                        <h6 class="mb-0 fs-12 font-weight-bold notification-dark-theme">{{ auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count() }} <span class="text-primary">New</span> Notification(s)</h6>
                                        <a href="#" class="mb-1 badge badge-primary ml-auto pl-3 pr-3 mark-read" id="mark-all">{{ __('Mark All Read') }}</a>
                                    </div>
                                    <div class="notify-menu">
                                        <div class="notify-menu-inner">
                                            @foreach ( auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification') as $notification )
                                                <div class="dropdown-item border-bottom pl-4 pr-4">
                                                    <div>
                                                        <a href="{{ route('user.notifications.show', [$notification->id]) }}" class="d-flex">
                                                            <div class="notifyimg bg-info-transparent text-info"> <i class="fa fa-bell fs-18"></i></div>
                                                            <div>
                                                                <div class="font-weight-bold fs-12 mt-2 notification-dark-theme">{{ __('New') }} {{ $notification->data['type'] }} {{ __('Notification') }}</div>
                                                                <div class="small text-muted fs-10">{{ $notification->created_at->diffForHumans() }}</div>
                                                            </div>                                            
                                                        </a>
                                                    </div>                                            
                                                </div>
                                            @endforeach                                
                                        </div>
                                    </div>
                                    <div class="view-all-button text-center">                            
                                        <a href="{{ route('user.notifications') }}" class="fs-12 font-weight-bold notification-dark-theme">{{ __('View All Notifications') }}</a>
                                    </div>                             
                                @else
                                    <div class="view-all-button text-center">
                                        <h6 class=" fs-12 font-weight-bold mb-1 notification-dark-theme">{{ __('There are no new notifications') }}</h6>                                    
                                    </div>
                                @endif
                            @endrole
                        @endif                        
                    </div>
                </div>
                <div class="dropdown items-center flex">
                    <a href="#" class="nav-link icon btn-theme-toggle">
                        <span class="header-icon fa-sharp fa-solid"></span>
                    </a>
                </div>
                <div class="dropdown header-expand" >
                    <a  class="nav-link icon" id="fullscreen-button">
                        <span class="header-icon fa-solid fa-expand" id="fullscreen-icon"></span>
                    </a>
                </div>
                <div class="dropdown header-languages mr-2">
                    <a class="nav-link icon" data-bs-toggle="dropdown">
                        <span class="header-icon fa-solid fa-globe"></span>
                    </a>
                    <div class="dropdown-menu animated">
                        <div class="local-menu">
                            @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                @if (in_array($localeCode, explode(',', $settings->languages)))
                                    <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" class="dropdown-item d-flex pl-4" hreflang="{{ $localeCode }}">
                                        <div>
                                            <span class="font-weight-normal fs-12">{{ ucfirst($properties['native']) }}</span> <span class="fs-10 text-muted">{{ $localeCode }}</span>
                                        </div>
                                    </a>   
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>                
                <div class="dropdown profile-dropdown">
                    <a href="#" class="nav-link" data-bs-toggle="dropdown">
                        <span class="float-right">
                            <img src="@if(auth()->user()->profile_photo_path){{ asset(auth()->user()->profile_photo_path) }} @else {{ theme_url('img/users/avatar.jpg') }} @endif" alt="img" class="avatar avatar-md">
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                        <div class="text-center pt-2">
                            <span class="text-center user fs-12 pb-0 font-weight-bold">{{ Auth::user()->name }}</span><br>
                            <span class="text-center fs-12 text-muted">{{ __(Auth::user()->job_role) }}</span>
                            <div class="dropdown-divider mt-3"></div>    
                        </div>
                        <a class="dropdown-item d-flex" href="{{ route('user.plans') }}">
                            <span class="profile-icon fa-solid fa-box-circle-check"></span>
                            <div class="fs-12">{{ __('Subscription Plans') }}</div>
                        </a>        
                        <a class="dropdown-item d-flex" href="{{ route('user.workbooks') }}">
                            <span class="profile-icon fa-solid fa-folder-bookmark"></span>
                            <div class="fs-12">{{ __('My Workbooks') }}</div>
                        </a> 
                        @if (config('payment.referral.enabled') == 'on')
                            <a class="dropdown-item d-flex" href="{{ route('user.referral') }}">
                                <span class="profile-icon fa-solid fa-badge-dollar"></span>
                                <span class="fs-12">{{ __('Affiliate Program') }}</span></a>
                            </a>
                        @endif                        
                        <a class="dropdown-item d-flex" href="{{ route('user.purchases') }}">
                            <span class="profile-icon fa-solid fa-money-check-pen"></span>
                            <span class="fs-12">{{ __('Orders') }}</span></a>
                        </a>
                        @if (config('settings.user_support') == 'enabled')
                            <a class="dropdown-item d-flex" href="{{ route('user.support') }}">
                                <span class="profile-icon fa-solid fa-headset"></span>
                                <div class="fs-12">{{ __('Support Request') }}</div>
                            </a>
                        @endif        
                        @if (config('settings.user_notification') == 'enabled')
                            <a class="dropdown-item d-flex" href="{{ route('user.notifications') }}">
                                <span class="profile-icon fa-solid fa-message-exclamation"></span>
                                <div class="fs-12">{{ __('Notifications') }}</div>
                                @if (auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count())
                                    <span class="badge badge-warning ml-3">{{ auth()->user()->unreadNotifications->where('type', 'App\Notifications\GeneralNotification')->count() }}</span>
                                @endif   
                            </a>
                        @endif 
                        <a class="dropdown-item d-flex" href="{{ route('user.profile') }}">
                            <span class="profile-icon fa-solid fa-id-badge"></span>
                            <span class="fs-12">{{ __('Profile Settings') }}</span></a>
                        </a>
                        <a class="dropdown-item d-flex" href="{{ route('logout') }}" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();"> 
                            <span class="profile-icon fa-solid fa-right-from-bracket"></span>          
                            <div class="fs-12">{{ __('Logout') }}</div>                            
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
            <!-- END MENU BAR -->
        </div>
    </div>
</div>
<!-- END TOP MENU BAR -->
