<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class UpgradeCheckpoint extends Model
{
    protected $table = 'lwa_upgrade_checkpoints';

    protected $guarded = ['id'];

    protected $casts = ['metadata' => 'array'];
}
