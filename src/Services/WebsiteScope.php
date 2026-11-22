<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Ensures every v3 analytics query is website-scoped. The active website is
 * resolved from config for now; HTTP/API resolvers will replace it in beta.3.
 */
class WebsiteScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $websiteId = config('laravel-web-analytics.v3.website_id');

        if ($websiteId !== null && $websiteId !== '') {
            $builder->where($model->qualifyColumn('website_id'), (int) $websiteId);
        }
    }
}
