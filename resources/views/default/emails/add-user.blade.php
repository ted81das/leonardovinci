<x-mail::message>
{!! $email->message !!}

<x-mail::panel>
<span style="font-weight: 700;"> {{ $newemail }} </span>
</x-mail::panel>

<x-mail::panel>
<span style="font-weight: 700;"> {{ $newpassword }} </span>
</x-mail::panel>


{!! $email->footer!!}
</x-mail::message>
