<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class BackendReadiness extends Command
{
    protected $signature = 'backend:readiness {--json : Emit machine-readable JSON}';

    protected $description = 'Validate the frozen LBB backend contract and internal readiness gate';

    public function handle(): int
    {
        $checks = [
            'contract_version' => $this->check(
                config('lbb.api.contract_version') === '2026-07-20-phase-16',
                (string) config('lbb.api.contract_version'),
            ),
            'backend_gate' => $this->check(
                config('lbb.launch.internal_gates.backend_complete.status') === 'ready',
                (string) config('lbb.launch.internal_gates.backend_complete.status'),
            ),
            'openapi' => $this->openApiCheck(),
            'database' => $this->databaseCheck(),
            'pagination_policy' => $this->check(
                config('lbb.policies.pagination.catalog_max') === 48
                    && config('lbb.policies.pagination.account_max') === 30,
                'catalog=48, account=30',
            ),
            'queue_policy' => $this->check(
                filled(config('lbb.policies.queue.connection')),
                (string) config('lbb.policies.queue.connection'),
            ),
            'storage_policy' => $this->check(
                filled(config('lbb.policies.storage.media_disk')),
                (string) config('lbb.policies.storage.media_disk'),
            ),
            'legacy_production_boundary' => $this->check(
                ! app()->environment('production') || ! config('lbb.legacy.enabled'),
                config('lbb.legacy.enabled') ? 'enabled' : 'disabled',
            ),
            'external_inputs_boundary' => $this->check(
                count(config('lbb.launch.external_only', [])) === 3,
                'exactly-three-external-inputs',
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
            $ready
                ? $this->info('LBB backend contract is frozen and ready for Phase 17 integration.')
                : $this->error('LBB backend readiness failed.');
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
                && ! isset($document['paths']['/api/v1/products']);

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
