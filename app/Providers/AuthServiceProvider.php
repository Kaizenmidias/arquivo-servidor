<?php

namespace App\Providers;

use App\Models\CustomScript;
use App\Models\Integration;
use App\Policies\CustomScriptPolicy;
use App\Policies\IntegrationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Integration::class => IntegrationPolicy::class,
        CustomScript::class => CustomScriptPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
