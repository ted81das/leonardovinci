<x-mail::message>

One of your customers have uploaded payment confirmation for his subscription via Bank Transfer. 

Order ID: <span style="font-weight: 600">{{ $order->order_id }}</span>

Make sure to approve his offline transaction so that he gets the credits and becomes a subscriber. 


Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
