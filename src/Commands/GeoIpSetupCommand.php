<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class GeoIpSetupCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:geoip'];

    protected $signature = 'lwa:geoip
                            {--install : Install the optional maxmind-db/reader package}
                            {--download : Download GeoLite2-City.mmdb using a MaxMind license key}
                            {--license-key= : MaxMind license key (or set MAXMIND_LICENSE_KEY)}
                            {--account-id= : MaxMind account ID (or set MAXMIND_ACCOUNT_ID), if required}
                            {--path= : Override the target .mmdb path}
                             {--auto : Scheduled mode - download only when the database is missing or stale}';

    protected $description = 'Set up offline MaxMind GeoIP for the visitors map';

    public function handle(): int
    {
        if ($this->option('auto')) {
            return $this->autoUpdate();
        }

        $this->output->title('Laravel Web Analytics — Offline GeoIP setup');

        if ($this->option('install')) {
            $this->installReader();
        }

        $rows = [];
        $readerOk = class_exists(\MaxMind\Db\Reader::class);
        $rows[] = ['Reader library', $readerOk ? '<info>OK</info>' : '<comment>MISSING</comment>',
            $readerOk ? 'maxmind-db/reader is installed' : 'Run: php artisan lwa:geoip --install'];

        $provider = (string) config('laravel-web-analytics.geoip.provider', '');
        $rows[] = ['Provider', $provider === 'maxmind' ? '<info>OK</info>' : '<comment>WARN</comment>',
            $provider === 'maxmind' ? 'maxmind' : "Currently '{$provider}' — set WEB_ANALYTICS_GEOIP_PROVIDER=maxmind"];

        $path = (string) ($this->option('path') ?: config('laravel-web-analytics.geoip.maxmind_db_path'));
        $dbExists = $path !== '' && is_file($path) && is_readable($path);
        $rows[] = ['Database file', $dbExists ? '<info>OK</info>' : '<comment>MISSING</comment>',
            $dbExists ? $path . ' (' . round(filesize($path) / 1048576, 1) . ' MB)' : ($path ?: 'No path configured')];

        if ($this->option('download')) {
            $this->downloadDatabase($path);
            $dbExists = $path !== '' && is_file($path) && is_readable($path);
            $rows[count($rows) - 1] = ['Database file', $dbExists ? '<info>OK</info>' : '<error>FAIL</error>',
                $dbExists ? $path . ' (' . round(filesize($path) / 1048576, 1) . ' MB)' : 'Download failed'];
        }

        $this->output->table(['Component', 'Status', 'Detail'], $rows);

        if (!$readerOk || !$dbExists || $provider !== 'maxmind') {
            $this->output->warning('GeoIP map is inactive. Fix the items above, then reload the Overview page.');
            return self::FAILURE;
        }

        $this->output->success('Offline GeoIP is ready. Country, city and coordinates are resolved locally.');

        return self::SUCCESS;
    }

    /**
     * Scheduled mode: respect the config switch and only download when the
     * database is missing or older than the configured interval.
     */
    private function autoUpdate(): int
    {
        if (!config('laravel-web-analytics.geoip.auto_download', false)) {
            $this->line('Automatic GeoIP download is disabled (WEB_ANALYTICS_GEOIP_AUTO_DOWNLOAD).');

            return self::SUCCESS;
        }

        $path = (string) config('laravel-web-analytics.geoip.maxmind_db_path', '');
        if ($path === '') {
            $this->output->warning('WEB_ANALYTICS_MAXMIND_DB is not configured.');

            return self::SUCCESS;
        }

        $days = max(1, (int) config('laravel-web-analytics.geoip.update_days', 7));
        $stale = !is_file($path) || (time() - filemtime($path)) > ($days * 86400);

        if (!$stale) {
            $this->line('GeoIP database is up to date (' . round((time() - filemtime($path)) / 86400, 1) . ' days old).');

            return self::SUCCESS;
        }

        $license = (string) (config('laravel-web-analytics.geoip.license_key') ?: env('MAXMIND_LICENSE_KEY', ''));
        if ($license === '') {
            $this->output->warning('GeoIP database is stale but no MAXMIND_LICENSE_KEY is configured.');

            return self::SUCCESS;
        }

        $accountId = (string) (config('laravel-web-analytics.geoip.account_id') ?: env('MAXMIND_ACCOUNT_ID', ''));
        $ok = $this->downloadDatabase($path, $license, $accountId);

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function installReader(): void
    {
        if (class_exists(\MaxMind\Db\Reader::class)) {
            $this->line('maxmind-db/reader is already installed.');
            return;
        }

        $this->line('Installing maxmind-db/reader in ' . base_path() . ' ...');
        $process = new Process(['composer', 'require', 'maxmind-db/reader', '--no-interaction'], base_path(), null, null, 300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->output->error('Composer could not install the reader. Install it manually: composer require maxmind-db/reader');
        }
    }

    private function downloadDatabase(string $path, ?string $license = null, ?string $accountId = null): bool
    {
        $license ??= (string) ($this->option('license-key') ?: config('laravel-web-analytics.geoip.license_key') ?: env('MAXMIND_LICENSE_KEY', ''));
        if ($license === '') {
            $this->output->error('A MaxMind license key is required (--license-key or MAXMIND_LICENSE_KEY).');
            return false;
        }
        if ($path === '') {
            $this->output->error('Configure WEB_ANALYTICS_MAXMIND_DB or pass --path first.');
            return false;
        }

        $archive = tempnam(sys_get_temp_dir(), 'lwa_maxmind_') . '.tar.gz';
        $accountId ??= (string) ($this->option('account-id') ?: config('laravel-web-analytics.geoip.account_id') ?: env('MAXMIND_ACCOUNT_ID', ''));
        $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City'
            . '&license_key=' . urlencode($license)
            . ($accountId !== '' ? '&account_id=' . urlencode($accountId) : '')
            . '&suffix=tar.gz';

        try {
            $response = Http::withOptions(['sink' => $archive, 'timeout' => 180])->get($url);

            if (!$response->successful()) {
                $body = is_file($archive) ? (string) @file_get_contents($archive) : '';
                $snippet = trim(substr(preg_replace('/\s+/', ' ', strip_tags($body)) ?: '', 0, 180));
                throw new \RuntimeException('MaxMind returned HTTP ' . $response->status()
                    . ($snippet !== '' ? ': ' . $snippet : '. Check the license key / account ID and outbound HTTPS access.'));
            }

            if (!is_file($archive) || filesize($archive) < 1000) {
                throw new \RuntimeException('Empty download. Check outbound HTTPS access to download.maxmind.com.');
            }

            $handle = @fopen($archive, 'rb');
            $magic = $handle ? @fread($handle, 2) : '';
            if ($handle) {
                fclose($handle);
            }
            if ($magic !== "\x1f\x8b") {
                throw new \RuntimeException('Downloaded file is not a gzip archive — likely an error page. Verify license details.');
            }

            $tar = preg_replace('/\.tar\.gz$/', '.tar', $archive);
            $phar = new \PharData($archive);
            $phar->decompress();

            $found = false;
            foreach (new \RecursiveIteratorIterator(new \PharData($tar)) as $file) {
                if (str_ends_with($file->getFilename(), '.mmdb')) {
                    File::ensureDirectoryExists(dirname($path));
                    copy($file->getPathname(), $path);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \RuntimeException('No .mmdb file found in the downloaded archive.');
            }

            $this->line('GeoLite2-City.mmdb installed at ' . $path);

            return true;
        } catch (\Throwable $e) {
            $this->output->error('GeoIP download failed: ' . $e->getMessage());

            return false;
        } finally {
            @unlink($archive);
            if (isset($tar)) {
                @unlink($tar);
            }
        }
    }
}
