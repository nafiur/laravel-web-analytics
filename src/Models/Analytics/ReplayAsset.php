<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class ReplayAsset extends Model
{
    protected $table = 'lwa_replay_assets';

    protected $guarded = ['id'];

    protected $casts = [
        'bytes'     => 'integer',
        'ref_count' => 'integer',
    ];
}
