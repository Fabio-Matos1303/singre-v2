<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Client;

class MigrateLegacyServiceOrders extends Command
{
    protected $signature = 'legacy:migrate:service-orders {--chunk=1000}';
    protected $description = 'Migrate legacy service orders into v2 (headers only; items require specific mapping)';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $this->info('Starting service orders migration (headers)...');

        $legacy = DB::connection('legacy');

        // Abertas
        $processed = 0;
        $legacy->table('ordemservico_ativa')->orderBy('os_numero')->chunk($chunk, function ($rows) use (&$processed, $legacy) {
            foreach ($rows as $row) {
                $clientId = $this->resolveClientId($legacy, (int) ($row->os_cliente ?? 0));
                if (! $clientId) continue;

                $notes = trim(implode("\n\n", array_filter([
                    $row->os_observacao ?? null,
                    $row->os_defeito ?? null,
                    $row->os_servico ?? null,
                    $row->os_obsadicional ?? null,
                ])));
                $notes = $this->convertToUtf8($notes) ?: null;

                $total = $this->toDecimal($row->os_valor ?? 0);
                $openedAt = ($row->os_dataentrada && $row->os_dataentrada !== '0000-00-00') ? $row->os_dataentrada : now();
                $updatedAt = ($row->os_datainicio && $row->os_datainicio !== '0000-00-00' && strlen($row->os_datainicio) === 10) ? $row->os_datainicio : now();
                DB::table('service_orders')->insert([
                    'client_id' => $clientId,
                    'status' => 'open',
                    'notes' => $notes ?: null,
                    'total' => $total,
                    'legacy_number' => (string) ($row->os_numero ?? null),
                    'opened_at' => $openedAt,
                    'closed_at' => null,
                    'created_at' => $openedAt,
                    'updated_at' => $updatedAt,
                ]);
                $processed++;
            }
            $this->info("Processed open headers: {$processed}");
        });

        // Concluídas
        $processed = 0;
        $legacy->table('ordemservico_concluida')->orderBy('os_numero')->chunk($chunk, function ($rows) use (&$processed, $legacy) {
            foreach ($rows as $row) {
                $clientId = $this->resolveClientId($legacy, (int) ($row->os_cliente ?? 0));
                if (! $clientId) continue;

                $notes = trim(implode("\n\n", array_filter([
                    $row->os_observacao ?? null,
                    $row->os_defeito ?? null,
                    $row->os_servico ?? null,
                    $row->os_obsadicional ?? null,
                ])));
                $notes = $this->convertToUtf8($notes) ?: null;

                $total = $this->toDecimal($row->os_valor ?? 0);
                $openedAt = $this->sanitizeDate($row->os_dataentrada) ?? ($this->sanitizeDate($row->os_dataconclusao) ?? now());
                $closedAt = $this->sanitizeDate($row->os_dataconclusao) ?? now();
                DB::table('service_orders')->insert([
                    'client_id' => $clientId,
                    'status' => 'closed',
                    'notes' => $notes ?: null,
                    'total' => $total,
                    'legacy_number' => (string) ($row->os_numero ?? null),
                    'opened_at' => $openedAt,
                    'closed_at' => $closedAt,
                    'created_at' => $openedAt,
                    'updated_at' => $closedAt,
                ]);
                $processed++;
            }
            $this->info("Processed closed headers: {$processed}");
        });

        $this->info('Service orders headers migration done.');
        $this->warn('Note: items (products/services) require explicit mapping from legacy item tables.');
        return self::SUCCESS;
    }

    private function toDecimal($value): string
    {
        if ($value === null) return '0.00';
        $str = (string) $value;
        $str = str_replace(['.', ' '], ['', ''], $str);
        $str = str_replace(',', '.', $str);
        return number_format((float) $str, 2, '.', '');
    }

    private function resolveClientId($legacy, int $legacyClientCode): ?int
    {
        if ($legacyClientCode <= 0) return null;
        $cli = $legacy->table('cadastrocliente')->where('cli_codigo', $legacyClientCode)->first();
        if (! $cli) return null;
        $email = $cli->cli_email ?? null;
        $document = $cli->cli_cnpjcpf ?? null;
        $name = trim($cli->cli_nomefantasia ?? $cli->cli_razaosocial ?? 'Sem Nome');
        $email = $email ? $this->convertToUtf8($email) : null;
        $name = $this->convertToUtf8($name);

        $query = DB::table('clients');
        if ($email) {
            $id = $query->where('email', $email)->value('id');
            if ($id) return (int) $id;
        }
        if ($document) {
            $id = DB::table('clients')->where('document', $document)->value('id');
            if ($id) return (int) $id;
        }
        // Create minimal client if not found
        return (int) Client::query()->create([
            'name' => $name,
            'email' => $email,
            'document' => $document,
        ])->id;
    }

    private function convertToUtf8(?string $value): ?string
    {
        if ($value === null) return null;
        $looksUtf8 = mb_check_encoding($value, 'UTF-8');
        return $looksUtf8 ? $value : mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    }

    private function sanitizeDate($value): ?string
    {
        if (!is_string($value) || trim($value) === '' || $value === '0000-00-00') return null;
        // Match Y-m-d with 1 to 4 digits for year
        if (preg_match('/^(\d{1,4})-(\d{2})-(\d{2})$/', $value, $m)) {
            $y = $m[1]; $mo = $m[2]; $d = $m[3];
            if (strlen($y) === 4) {
                $yi = (int) $y;
                if ($yi < 1900 || $yi > 2099) {
                    // Coerce to 20xx using last two digits
                    $y = '20' . substr($y, -2);
                }
            } elseif (strlen($y) === 3) {
                $y = '2' . $y; // e.g., 207 -> 2207 (still wrong), fix to 2007 using last two
                $y = '20' . substr($y, -2);
            } elseif (strlen($y) <= 2) {
                $y = '20' . str_pad($y, 2, '0', STR_PAD_LEFT);
            }
            // Basic range validation for month/day
            if ((int)$mo < 1 || (int)$mo > 12) return null;
            if ((int)$d < 1 || (int)$d > 31) return null;
            return sprintf('%s-%s-%s', $y, $mo, $d);
        }
        return null;
    }
}
