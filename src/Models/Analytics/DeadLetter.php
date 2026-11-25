<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class DeadLetter extends Model
{
    protected $table = 'lwa_dead_letters';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }
}
