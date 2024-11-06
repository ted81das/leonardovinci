<x-mail::message>

{!! $email->message !!}


{!! $email->footer !!}

<div style="text-align: center">
    <span class="text-muted" style="font-size: 10px; text-align: center">{{ __('If you would like to unsubscribe from receiving newsletters please') }} <a href="{{ route('email.unsubscribe.show', ['email' => $user]) }}">{{ __('click here') }}</a></span>
</div>

</x-mail::message>
