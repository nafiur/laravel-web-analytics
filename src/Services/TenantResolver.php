<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Http\Request;

class TenantResolver
{
    /**
     * Check if multi-tenancy tracking is enabled.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('laravel-web-analytics.multi_tenancy.enabled', false);
    }

    /**
     * Resolve the current tenant identifier.
     */
    public static function resolve(?Request $request = null): ?string
    {
        if (!self::isEnabled()) {
            return null;
        }

        $request = $request ?: (function_exists('request') ? request() : null);

        // 1. Explicit custom resolver (Closure, Callable, or Class name)
        $customResolver = config('laravel-web-analytics.multi_tenancy.tenant_resolver');
        if ($customResolver) {
            try {
                if (is_callable($customResolver)) {
                    $tenantId = call_user_func($customResolver, $request);
                    if ($tenantId !== null && $tenantId !== '') {
                        return (string) $tenantId;
                    }
                } elseif (is_string($customResolver) && class_exists($customResolver)) {
                    $instance = app($customResolver);
                    $tenantId = method_exists($instance, 'resolve') 
                        ? $instance->resolve($request) 
                        : $instance($request);
                    if ($tenantId !== null && $tenantId !== '') {
                        return (string) $tenantId;
                    }
                }
            } catch (\Throwable $e) {
                // Silently fall through to auto-detect
            }
        }

        // 2. Auto-detect Stancl Tenancy (stancl/tenancy)
        if (function_exists('tenant')) {
            try {
                $tenant = tenant();
                if ($tenant) {
                    $id = is_object($tenant) && method_exists($tenant, 'getTenantKey') 
                        ? $tenant->getTenantKey() 
                        : tenant('id');
                    if ($id !== null && $id !== '') {
                        return (string) $id;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 3. Auto-detect Spatie Laravel Multitenancy (spatie/laravel-multitenancy)
        if (class_exists('\Spatie\Multitenancy\Models\Tenant')) {
            try {
                $tenant = \Spatie\Multitenancy\Models\Tenant::current();
                if ($tenant && $tenant->getKey()) {
                    return (string) $tenant->getKey();
                }
            } catch (\Throwable $e) {}
        }

        // 4. Auto-detect from bound Tenancy Service in Service Container
        try {
            if (function_exists('app')) {
                if (app()->bound('tenancy')) {
                    $tenancy = app('tenancy');
                    if (is_object($tenancy)) {
                        if (method_exists($tenancy, 'getStoreId') && ($id = $tenancy->getStoreId())) {
                            return (string) $id;
                        }
                        if (method_exists($tenancy, 'getTenantId') && ($id = $tenancy->getTenantId())) {
                            return (string) $id;
                        }
                        if (method_exists($tenancy, 'getStore') && ($store = $tenancy->getStore())) {
                            return (string) ($store->id ?? $store->getKey());
                        }
                    }
                }
                if (app()->bound('tenant')) {
                    $tenant = app('tenant');
                    if (is_object($tenant) && method_exists($tenant, 'getKey')) {
                        return (string) $tenant->getKey();
                    }
                    if (is_scalar($tenant) && $tenant !== '') {
                        return (string) $tenant;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 5. Auto-detect Spatie Permission Teams
        if (function_exists('getPermissionsTeamId')) {
            try {
                $teamId = getPermissionsTeamId();
                if ($teamId !== null && $teamId !== '') {
                    return (string) $teamId;
                }
            } catch (\Throwable $e) {}
        }

        // 6. Auto-detect from Active Session
        try {
            if (function_exists('session')) {
                foreach (['active_store_id', 'tenant_id', 'store_id'] as $sessionKey) {
                    if (session()->has($sessionKey)) {
                        $val = session($sessionKey);
                        if ($val !== null && $val !== '') {
                            return (string) $val;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 7. Auto-detect from Authenticated User
        try {
            if (function_exists('auth') && auth()->check()) {
                $user = auth()->user();
                if (!empty($user->tenant_id)) {
                    return (string) $user->tenant_id;
                }
                if (!empty($user->store_id)) {
                    return (string) $user->store_id;
                }
                if (!empty($user->active_store_id)) {
                    return (string) $user->active_store_id;
                }
                if (!empty($user->current_team_id)) {
                    return (string) $user->current_team_id;
                }
                if (isset($user->tenant) && is_object($user->tenant) && !empty($user->tenant->id)) {
                    return (string) $user->tenant->id;
                }
                if (isset($user->store) && is_object($user->store) && !empty($user->store->id)) {
                    return (string) $user->store->id;
                }
            }
        } catch (\Throwable $e) {}

        // 8. Auto-detect from Request (Route param, header, or attribute)
        if ($request) {
            try {
                if ($header = $request->header('X-Tenant-ID') ?: $request->header('X-Tenant') ?: $request->header('X-Store-ID')) {
                    return (string) $header;
                }

                if ($attr = $request->attributes->get('tenant_id') ?: $request->attributes->get('store_id')) {
                    return (string) $attr;
                }

                $routeTenant = $request->route('tenant') ?: $request->route('store');
                if ($routeTenant) {
                    return is_object($routeTenant) && method_exists($routeTenant, 'getKey') 
                        ? (string) $routeTenant->getKey() 
                        : (string) $routeTenant;
                }
            } catch (\Throwable $e) {}
        }

        return null;
    }
}
