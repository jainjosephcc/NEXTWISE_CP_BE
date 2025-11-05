<?php

// app/Http/Controllers/Api/HealthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function index()
    {
        $checks = [];

        // PostgreSQL (default)
        try {
            $t0 = microtime(true);
            DB::select('SELECT 1'); // light ping
            $checks['postgres'] = [
                'ok' => true,
                'latency_ms' => round((microtime(true) - $t0) * 1000, 1),
                'connection' => config('database.default')
            ];
        } catch (\Throwable $e) {
            $checks['postgres'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // MySQL CRM
        try {
            $t0 = microtime(true);
            DB::connection('mysql_crm')->select('SELECT 1');
            $checks['mysql_crm'] = [
                'ok' => true,
                'latency_ms' => round((microtime(true) - $t0) * 1000, 1),
                'connection' => 'mysql_crm'
            ];
        } catch (\Throwable $e) {
            $checks['mysql_crm'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        return response()->json([
            'ok' => collect($checks)->every(fn($c) => $c['ok'] ?? false),
            'checks' => $checks,
            'app' => [
                'env' => config('app.env'),
                'debug' => (bool) config('app.debug'),
                'version' => app()->version(),
            ],
            'time' => now()->toIso8601String(),
        ]);
    }

    public function full()
    {
        $payload = $this->index()->getData(true);

        // Cache
        try {
            Cache::put('health:ping', 'ok', 10);
            $payload['checks']['cache'] = ['ok' => Cache::get('health:ping') === 'ok'];
        } catch (\Throwable $e) {
            $payload['checks']['cache'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // Redis
        try {
            $pong = Redis::connection()->ping();
            $payload['checks']['redis'] = ['ok' => str_contains(strtolower($pong), 'pong')];
        } catch (\Throwable $e) {
            $payload['checks']['redis'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // Storage writable
        try {
            $test = 'health_'.uniqid().'.txt';
            Storage::disk('local')->put($test, 'ok');
            Storage::disk('local')->delete($test);
            $payload['checks']['storage'] = ['ok' => true];
        } catch (\Throwable $e) {
            $payload['checks']['storage'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        $payload['ok'] = collect($payload['checks'])->every(fn($c) => $c['ok'] ?? false);
        return response()->json($payload);
    }
}
