<?php

namespace Nafiur\WebAnalytics\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulateLiveTrafficCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lwa:live 
                            {--rps=2 : Requests generated per second} 
                            {--interval=1 : Delay in seconds between generation ticks} 
                            {--count=0 : Total number of ticks to run (0 for continuous)}
                            {--tenant= : Optional tenant ID to tag traffic}';

    /**
     * The console command description.
     */
    protected $description = 'Generate realistic live web traffic every second to test real-time dashboard and SSE stream';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!\Nafiur\WebAnalytics\Services\DatabaseReadiness::legacyReady()) {
            $this->error('Legacy table ' . \Nafiur\WebAnalytics\Services\DatabaseReadiness::legacyTable() . ' does not exist.');
            $this->line('Run: php artisan migrate');
            return self::FAILURE;
        }

        $tableName = config('laravel-web-analytics.table_name', 'lwa_logs');
        $rps = max(1, (int) $this->option('rps'));
        $interval = max(1, (int) $this->option('interval'));
        $maxCount = (int) $this->option('count');
        $tenantId = $this->option('tenant') ?: null;

        $this->info("------------------------------------------------------------------");
        $this->info(" 🚀 Live Web Traffic Generator for Laravel Web Analytics");
        $this->info(" Targeting table: [{$tableName}] | RPS: {$rps} req/sec | Interval: {$interval}s");
        if ($tenantId) {
            $this->info(" Scoped to Tenant: [{$tenantId}]");
        }
        $this->info(" Open your browser to /web-analytics and click 'Live Stream' tab!");
        $this->info(" Press Ctrl+C to stop.");
        $this->info("------------------------------------------------------------------");

        $locations = [
            ['country' => 'Bangladesh', 'city' => 'Dhaka', 'ip' => '103.230.104.'],
            ['country' => 'Bangladesh', 'city' => 'Chittagong', 'ip' => '103.48.16.'],
            ['country' => 'United States', 'city' => 'New York', 'ip' => '142.250.190.'],
            ['country' => 'United States', 'city' => 'San Francisco', 'ip' => '172.217.16.'],
            ['country' => 'United Kingdom', 'city' => 'London', 'ip' => '185.199.108.'],
            ['country' => 'Germany', 'city' => 'Berlin', 'ip' => '178.63.50.'],
            ['country' => 'India', 'city' => 'Bengaluru', 'ip' => '106.51.242.'],
            ['country' => 'Canada', 'city' => 'Toronto', 'ip' => '198.51.100.'],
            ['country' => 'Japan', 'city' => 'Tokyo', 'ip' => '133.242.18.'],
        ];

        $devices = [
            ['device' => 'Desktop', 'os' => 'Windows', 'browser' => 'Chrome', 'ua' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/129.0'],
            ['device' => 'Desktop', 'os' => 'Mac', 'browser' => 'Safari', 'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_7) Safari/605.1'],
            ['device' => 'Mobile', 'os' => 'Android', 'browser' => 'Chrome', 'ua' => 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36'],
            ['device' => 'Mobile', 'os' => 'iOS', 'browser' => 'Safari', 'ua' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_6) Safari/604.1'],
        ];

        $routes = [
            ['path' => '/', 'method' => 'GET', 'status' => 200, 'min_b' => 850000, 'max_b' => 2400000],
            ['path' => '/posts', 'method' => 'GET', 'status' => 200, 'min_b' => 1200000, 'max_b' => 3800000],
            ['path' => '/posts/building-realtime-web-analytics-in-laravel', 'method' => 'GET', 'status' => 200, 'min_b' => 2500000, 'max_b' => 8500000],
            ['path' => '/posts/mastering-eloquent-query-optimization', 'method' => 'GET', 'status' => 200, 'min_b' => 1800000, 'max_b' => 6200000],
            ['path' => '/blogs', 'method' => 'GET', 'status' => 200, 'min_b' => 950000, 'max_b' => 3100000],
            ['path' => '/blogs/announcing-laravel-web-analytics-v1', 'method' => 'GET', 'status' => 200, 'min_b' => 2200000, 'max_b' => 7400000],
            ['path' => '/categories', 'method' => 'GET', 'status' => 200, 'min_b' => 350000, 'max_b' => 950000],
            ['path' => '/downloads/starter-kit.zip', 'method' => 'GET', 'status' => 200, 'min_b' => 18000000, 'max_b' => 55000000],
            ['path' => '/api/v1/metrics', 'method' => 'GET', 'status' => 200, 'min_b' => 150000, 'max_b' => 450000],
            ['path' => '/wp-login.php', 'method' => 'GET', 'status' => 404, 'min_b' => 25000, 'max_b' => 85000],
        ];

        $baseUrl = config('app.url', 'http://localhost:8000');
        $tick = 0;

        while (true) {
            $now = Carbon::now();
            $batch = [];

            for ($i = 0; $i < $rps; $i++) {
                $loc = $locations[array_rand($locations)];
                $dev = $devices[array_rand($devices)];
                $route = $routes[array_rand($routes)];

                $ip = $loc['ip'] . mt_rand(2, 254);
                $isAuth = (mt_rand(1, 10) <= 3);
                $bytes = mt_rand($route['min_b'], $route['max_b']);
                $responseTime = round(mt_rand(250, 4500) / 10, 2);

                $batch[] = [
                    'tenant_id'        => $tenantId,
                    'session_id'       => $isAuth ? 'u_1' : 's_' . Str::random(32),
                    'ip_address'       => $ip,
                    'method'           => $route['method'],
                    'path'             => $route['path'],
                    'full_url'         => rtrim($baseUrl, '/') . $route['path'],
                    'status_code'      => $route['status'],
                    'response_time_ms' => $responseTime,
                    'bytes_sent'       => $bytes,
                    'country'          => $loc['country'],
                    'city'             => $loc['city'],
                    'browser'          => $dev['browser'],
                    'os'               => $dev['os'],
                    'device'           => $dev['device'],
                    'referrer'         => 'Direct',
                    'user_agent'       => $dev['ua'],
                    'user_id'          => $isAuth ? 1 : null,
                    'created_at'       => $now->toDateTimeString(),
                    'updated_at'       => $now->toDateTimeString(),
                ];
            }

            DB::table($tableName)->insert($batch);

            $last = end($batch);
            $mbSent = round($last['bytes_sent'] / (1024 * 1024), 2);
            $this->line(sprintf(
                "<fg=gray>[%s]</> <fg=green;options=bold>+%d live req</> | <fg=cyan>%s %s</> (<fg=yellow>%s</> - <fg=magenta>%s MB</> in %sms) from <fg=blue>%s, %s</>",
                $now->format('H:i:s'),
                $rps,
                $last['method'],
                $last['path'],
                $last['status_code'],
                $mbSent,
                $last['response_time_ms'],
                $last['city'],
                $last['country']
            ));

            $tick++;
            if ($maxCount > 0 && $tick >= $maxCount) {
                break;
            }

            sleep($interval);
        }

        $this->info("Live traffic simulation stopped.");
        return self::SUCCESS;
    }
}
