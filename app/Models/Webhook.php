<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Webhook extends Model
{
    protected $fillable = [
        'name', 'url', 'secret', 'events', 'is_active',
        'retry_count', 'last_triggered_at', 'last_status',
    ];
    protected $casts = [
        'events'            => 'array',
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    // SSRF protection
    private function isSafeUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;
        $ip = gethostbyname($host);
        $privateRanges = [
            '/^127\./',
            '/^10\./',
            '/^172\.(1[6-9]|2[0-9]|3[01])\./',
            '/^192\.168\./',
            '/^::1$/',
            '/^localhost$/i',
        ];
        foreach ($privateRanges as $range) {
            if (preg_match($range, $ip) || preg_match($range, $host)) return false;
        }
        return true;
    }

    public function trigger(string $event, array $payload = []): bool
    {
        if (!$this->is_active) return false;
        if (!in_array($event, $this->events ?? [])) return false;
        if (!$this->isSafeUrl($this->url)) {
            Log::warning("Webhook SSRF blocked: {$this->url}");
            return false;
        }

        $body = json_encode([
            'event'   => $event,
            'payload' => $payload,
            'time'    => now()->toISOString(),
        ]);
        $headers = ['Content-Type' => 'application/json'];
        if ($this->secret) {
            $headers['X-Webhook-Signature'] = 'sha256=' . hash_hmac('sha256', $body, $this->secret);
        }
        try {
            $response = Http::withHeaders($headers)->timeout(10)->post($this->url, json_decode($body, true));
            $this->update([
                'last_triggered_at' => now(),
                'last_status'       => $response->successful() ? 'success' : 'failed',
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            $this->update(['last_triggered_at' => now(), 'last_status' => 'error']);
            Log::warning("Webhook error [{$this->id}]: " . $e->getMessage());
            return false;
        }
    }

    public static function dispatch(string $event, array $payload = []): void
    {
        // queue بشه تا request رو block نکنه
        \App\Jobs\DispatchWebhooks::dispatch($event, $payload);
    }
}
