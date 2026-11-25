<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Nafiur\WebAnalytics\Models\Analytics\ReplayChunk;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;

class ReplayPruneCommand extends Command
{
    protected $signature = 'lwa:replay:prune {--days= : Retention window in days; defaults to replay.retention_days}';

    protected $description = 'Delete replay chunks, markers and sessions older than the configured retention window';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('laravel-web-analytics.replay.retention_days', 30));
        if ($days <= 0) {
            $this->error('Retention days must be greater than zero.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $diskName = (string) (config('laravel-web-analytics.replay.disk') ?: config('filesystems.default', 'local'));
        $disk = Storage::disk($diskName);

        $sessionIds = ReplaySession::where('created_at', '<', $cutoff)->pluck('id');
        $chunkPaths = ReplayChunk::whereIn('replay_session_id', $sessionIds)->pluck('disk_path');
        foreach ($chunkPaths as $path) {
            $disk->delete($path);
        }

        ReplayMarker::whereIn('replay_session_id', $sessionIds)->delete();
        ReplayChunk::whereIn('replay_session_id', $sessionIds)->delete();
        $deleted = ReplaySession::whereIn('id', $sessionIds)->delete();

        $this->info("Pruned {$deleted} replay session(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
