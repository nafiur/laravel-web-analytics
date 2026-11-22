<?php

namespace Nafiur\WebAnalytics\Models\V3;

use Illuminate\Database\Eloquent\Model;

class UpgradeCheckpoint extends Model
{
    protected $table = 'lwa_upgrade_checkpoints';

    protected $guarded = ['id'];

    protected $casts = ['metadata' => 'array'];
}
