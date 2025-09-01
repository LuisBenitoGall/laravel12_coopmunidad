<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use App\Listeners\FlushUserMenuCache;

class EventServiceProvider extends ServiceProvider{
    protected $listen = [
        RoleAttached::class         => [FlushUserMenuCache::class],
        RoleDetached::class         => [FlushUserMenuCache::class],
        PermissionAttached::class   => [FlushUserMenuCache::class],
        PermissionDetached::class   => [FlushUserMenuCache::class],
    ];
}
