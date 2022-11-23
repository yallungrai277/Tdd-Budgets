<x-mail::message>
# Test Mail

This is a test mail.

<x-mail::button :url="''">
Test Mail
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
