<x-mail::message>
# Ordem de Serviço #{{ $order->id }} criada

Cliente: **{{ $order->client->name }}** ({{ $order->client->email }})

Status: {{ $order->status }}

@component('mail::panel')
Total: R$ {{ number_format($order->total,2,',','.') }}
@endcomponent

Obrigado,<br>
{{ \App\Models\Setting::getValue('company.name', config('app.name')) }}
</x-mail::message>
