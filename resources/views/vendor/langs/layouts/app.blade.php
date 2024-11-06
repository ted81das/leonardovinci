@extends('layouts.app')
@section('css')
	<link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('page-header')
	<!-- EDIT PAGE HEADER -->
	<div class="page-header mt-5-7 justify-content-center">
		<div class="page-leftheader text-center">
			<h4 class="page-title mb-0">{{ __('Language Manager') }}</h4>
			<ol class="breadcrumb mb-2">
				<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fa fa-sliders mr-2 fs-12"></i>{{ __('Admin') }}</a></li>
				<li class="breadcrumb-item" aria-current="page"><a href="{{url('#')}}"> {{ __('General Settings') }}</a></li>
				<li class="breadcrumb-item active" aria-current="page"><a href="{{ route('elseyyid.translations.home2') }}"> {{ __('Languages') }}</a></li>
			</ol>
		</div>
	</div>
	<!-- END PAGE HEADER -->
@endsection

@section('content')
	<div class="row justify-content-center">
		<div class="col-lg-8 col-mg-12 col-sm-12">
			<div class="card border-0">				
				<div class="card-body pt-7 pl-7 pr-7 pb-6">		
                    @include('langs::includes.messages')
                    @yield('content_languages')						
				</div>
			</div>
		</div>
	</div>
    
	
@endsection

@yield('scripts')
@section('js')
	<script>
        $(document).ready(function() {
            
            var local = "{{ LaravelLocalization::getCurrentLocale() }}";

            $('.language-card .custom-switch-input').click(function() {
                var formData = new FormData();
                formData.append('lang', $(this).attr('id'));
                formData.append('state', $(this).prop('checked') ? 1 : 0);

                $.ajax({
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    url: "/app/admin/settings/languages/lang/save",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        toastr.success("{{ __('Saved successfully') }}");
                    },
                    error: function(data) {
                        var err = data.responseJSON.errors;
                        $.each(err, function(index, value) {
                            toastr.error(value);
                        });
                    }
                });

            });
        });
    </script>
@endsection