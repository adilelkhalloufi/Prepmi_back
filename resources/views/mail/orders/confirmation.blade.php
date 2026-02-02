<x-mail::message>
<x-slot name="subject">Order Confirmation - {{ $order->num_order }}</x-slot>

Hello {{ $order->first_name }} {{ $order->last_name }},

Thank you for your order! Your order has been successfully created.

**Order Details:**
- **Order Number:** {{ $order->num_order }}
- **Order Date:** {{ $order->date_order->format('d/m/Y H:i') }}
- **Status:** {{ $order->statue }}
- **Total Amount:** {{ $order->total_amount }} MAD

**Delivery Address:**
{{ $order->adresse_livrsion }}

@if($order->meals->count() > 0)
**Ordered Meals:**
@foreach($order->meals as $meal)
- {{ $meal->name }} (Quantity: {{ $meal->pivot->quantity }}, Price: {{ $meal->pivot->price }} MAD)
@endforeach
@endif

@if($order->deliveries->count() > 0)
**Delivery Schedule:**
@foreach($order->deliveries as $delivery)
- {{ $delivery->deliverySlot->slot_name }} ({{ $delivery->delivery_window_start->format('H:i') }} - {{ $delivery->delivery_window_end->format('H:i') }})
@endforeach
@endif

If you have any questions about your order, please contact our support team.

Thank you for choosing PrepMi!

Best regards,<br>
{{ config('app.name') }} Team
</x-mail::message>