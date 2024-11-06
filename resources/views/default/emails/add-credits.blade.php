<x-mail::message>
{!! $email->message !!}


<x-mail::panel>
# Total Available Words: @if ($words == -1) {{ __('Unlimited') }} @else {{ $words }} @endif
</x-mail::panel>

<x-mail::panel>
# Total Available Minutes: @if ($minutes == -1) {{ __('Unlimited') }} @else {{ $minutes }} @endif
</x-mail::panel>

<x-mail::panel>
# Total Available Characters: @if ($chars == -1) {{ __('Unlimited') }} @else {{ $chars }} @endif 
</x-mail::panel>

<x-mail::panel>
# Total Available Dalle Images: @if ($dalle_images == -1) {{ __('Unlimited') }} @else {{ $dalle_images }} @endif
</x-mail::panel>

<x-mail::panel>
# Total Available SD Images: @if ($sd_images == -1) {{ __('Unlimited') }} @else {{ $sd_images }} @endif 
</x-mail::panel>



{!! $email->footer !!}
</x-mail::message>
