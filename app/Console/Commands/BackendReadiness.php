<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class BackendReadiness extends Command
{
    protected $signature = 'backend:readiness {--json : Emit machine-readable JSON}';

    protected $description = 'Validate the current LBB backend contract and fail closed until apparel freeze';

    public function handle(): int
    {
        $checks = [
            'contract_version' => $this->check(
                config('lbb.api.contract_version') === '2026-08-07-f14-be-b2',
                (string) config('lbb.api.contract_version'),
            ),
            'domain_cleanup' => $this->check(
                config('lbb.contracts.domain_cleanup.status') === 'ready',
                (string) config('lbb.contracts.domain_cleanup.status'),
            ),
            'openapi' => $this->openApiCheck(),
            'database' => $this->databaseCheck(),
            'apparel_domain' => $this->check(
                config('lbb.contracts.apparel_domain.status') === 'ready',
                (string) config('lbb.contracts.apparel_domain.status'),
            ),
            'backend_freeze' => $this->check(
                config('lbb.contracts.backend_freeze.status') === 'ready',
                (string) config('lbb.contracts.backend_freeze.status'),
            ),
        ];

        $ready = collect($checks)->every(fn (array $check): bool => $check['ok']);
        $payload = [
            'ready' => $ready,
            'contractVersion' => config('lbb.api.contract_version'),
            'checks' => $checks,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(
                ['Check', 'Status', 'Detail'],
                collect($checks)->map(fn (array $check, string $name): array => [
                    $name,
                    $check['ok'] ? 'ready' : 'failed',
                    $check['detail'],
                ])->values()->all(),
            );
        }

        return $ready ? self::SUCCESS : self::FAILURE;
    }

    private function openApiCheck(): array
    {
        try {
            $path = (string) config('lbb.api.openapi_path');
            $document = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
            $valid = ($document['openapi'] ?? null) === '3.1.0'
                && ($document['info']['version'] ?? null) === config('lbb.api.contract_version')
                && isset($document['paths']['/api/system/openapi'])
                && ! isset($document['paths']['/api/catalog/products']);

            return $this->check($valid, $path);
        } catch (Throwable $exception) {
            return $this->check(false, $exception->getMessage());
        }
    }

    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->check(true, DB::connection()->getDriverName());
        } catch (Throwable $exception) {
            return $this->check(false, $exception->getMessage());
        }
    }

    private function check(bool $ok, string $detail): array
    {
        return ['ok' => $ok, 'detail' => $detail];
    }
}
