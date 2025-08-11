<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <style>
      body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
      h1 { font-size: 18px; margin: 0 0 10px; }
      table { width: 100%; border-collapse: collapse; margin-top: 10px; }
      th, td { border: 1px solid #000; padding: 6px; text-align: left; }
      .tot { text-align: right; }
    </style>
  </head>
  <body>
    <h1>{{ \App\Models\Setting::getValue('company.name', config('app.name')) }} - Ordem de Serviço #{{ $order->id }}</h1>
    <div>Cliente: {{ $order->client->name }} ({{ $order->client->email }})</div>
    <div>Contato: {{ \App\Models\Setting::getValue('company.email') }} | {{ \App\Models\Setting::getValue('company.phone') }}</div>
    <div>Status: {{ $order->status }} | Aberta em: {{ optional($order->opened_at)->format('d/m/Y H:i') }}</div>

    <h3>Produtos</h3>
    <table>
      <thead><tr><th>Produto</th><th>Qtd</th><th>Unit</th><th>Total</th></tr></thead>
      <tbody>
        @foreach ($order->products as $p)
          <tr>
            <td>{{ $p->name }}</td>
            <td class="tot">{{ $p->pivot->quantity }}</td>
            <td class="tot">{{ number_format($p->pivot->unit_price,2,',','.') }}</td>
            <td class="tot">{{ number_format($p->pivot->total,2,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h3>Serviços</h3>
    <table>
      <thead><tr><th>Serviço</th><th>Qtd</th><th>Unit</th><th>Total</th></tr></thead>
      <tbody>
        @foreach ($order->services as $s)
          <tr>
            <td>{{ $s->name }}</td>
            <td class="tot">{{ $s->pivot->quantity }}</td>
            <td class="tot">{{ number_format($s->pivot->unit_price,2,',','.') }}</td>
            <td class="tot">{{ number_format($s->pivot->total,2,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h2 class="tot">Total: {{ number_format($order->total,2,',','.') }}</h2>
  </body>
</html>
