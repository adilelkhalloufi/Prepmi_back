<x-mail::message>
<x-slot name="subject">Verify Your Email Address</x-slot>

Hello! {{ $user->name }},

Code :  {{ $user->code_verify }}

You are receiving this email because we received a registration request for your email address.
If you did not make this request,
you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
