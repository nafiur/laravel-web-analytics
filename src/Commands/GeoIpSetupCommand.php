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
                            {--path= : Override the target .mmdb path}';

    protected $description = 'Set up offline MaxMind GeoIP for the visitors map';

    public function handle(): int
    {
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

    private function downloadDatabase(string $path): void
    {
        $license = (string) ($this->option('license-key') ?: env('MAXMIND_LICENSE_KEY', ''));
        if ($license === '') {
            $this->output->error('A MaxMind license key is required (--license-key or MAXMIND_LICENSE_KEY).');
            return;
        }
        if ($path === '') {
            $this->output->error('Configure WEB_ANALYTICS_MAXMIND_DB or pass --path first.');
            return;
        }

        $archive = tempnam(sys_get_temp_dir(), 'lwa_maxmind_') . '.tar.gz';
        $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City'
            . '&license_key=' . urlencode($license) . '&suffix=tar.gz';

        try {
            Http::withOptions(['sink' => $archive, 'timeout' => 180])->get($url);

            if (!is_file($archive) || filesize($archive) < 1000) {
                throw new \RuntimeException('Download failed or returned an empty/invalid file. Check the license key.');
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
        } catch (\Throwable $e) {
            $this->output->error('GeoIP download failed: ' . $e->getMessage());
        } finally {
            @unlink($archive);
            if (isset($tar)) {
                @unlink($tar);
            }
        }
    }
}
