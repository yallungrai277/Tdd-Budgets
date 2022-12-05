<x-mail::message>
# Hi {{$order->user?->name}}

Your order has been confirmed. You can view your orders from here.

<x-mail::button :url="route('orders.index')">
View orders
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
