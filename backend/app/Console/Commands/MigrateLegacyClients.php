<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Client;

class MigrateLegacyClients extends Command
{
    protected $signature = 'legacy:migrate:clients {--chunk=1000}';
    protected $description = 'Migrate legacy clients from starnew.cadastrocliente to v2 clients';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $this->info('Starting clients migration...');

        $legacy = DB::connection('legacy');
        $total = (int) $legacy->table('cadastrocliente')->count();
        $this->info("Legacy clients found: {$total}");

        $processed = 0;
        $legacy->table('cadastrocliente')->orderBy('cli_codigo')->chunkById($chunk, function ($rows) use (&$processed) {
            foreach ($rows as $row) {
                $name = trim($row->cli_nomefantasia ?? $row->cli_razaosocial ?? 'Sem Nome');
                $email = $row->cli_email ?? null;
                if ($email !== null) {
                    $email = trim((string) $email);
                    if ($email === '') { $email = null; }
                }
                $phone = $row->cli_telefone ?? ($row->cli_telefoneaux ?? null);
                $document = $row->cli_cnpjcpf ?? null;
                $address = $row->cli_endereco ?? '';
                $address = trim($address);

                // Best-effort encoding fix (legacy latin1 to utf8)
                $name = $this->convertToUtf8($name);
                $address = $this->convertToUtf8($address);
                if ($email) { $email = $this->convertToUtf8($email); }

                // Upsert by a deterministic key: prefer email, else legacy_id
                $client = Client::query()->firstOrNew(['email' => $email]);
                if (! $email) {
                    $client = Client::query()->firstOrNew(['name' => $name, 'document' => $document]);
                }

                $client->fill([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'document' => $document,
                    'address' => $address,
                ]);

                $client->save();
                $processed++;
            }
            $this->info("Processed: {$processed}");
        }, 'cli_codigo');

        $this->info('Clients migration done.');
        return self::SUCCESS;
    }

    private function convertToUtf8(?string $value): ?string
    {
        if ($value === null) return null;
        // If it looks already UTF-8, keep it; otherwise convert from latin1
        $looksUtf8 = mb_check_encoding($value, 'UTF-8');
        return $looksUtf8 ? $value : mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    }
}
