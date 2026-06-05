<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->admin_enabled) {
            abort(403);
        }

        if ($user->role !== 'admin') {
            $permissions = $user->permissions;
            $permissions = is_array($permissions) ? array_values(array_filter(array_map('strval', $permissions))) : [];

            $routeName = $request->route()?->getName();
            $module = $this->moduleForRouteName(is_string($routeName) ? $routeName : null);

            if ($module && !in_array($module, $permissions, true)) {
                abort(403);
            }
        }

        return $next($request);
    }

    private function moduleForRouteName(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        if (str_starts_with($routeName, 'admin.profile')) {
            return null;
        }

        if ($routeName === 'admin.dashboard') return 'dashboard';

        if (str_starts_with($routeName, 'admin.properties')) return 'properties';
        if (str_starts_with($routeName, 'admin.property-types')) return 'properties';
        if (str_starts_with($routeName, 'admin.special-categories')) return 'properties';

        if (str_starts_with($routeName, 'admin.business-types')) return 'business_types';

        if (str_starts_with($routeName, 'admin.pages')) return 'pages';
        if (str_starts_with($routeName, 'admin.blog.')) return 'pages';

        if (str_starts_with($routeName, 'admin.appearance')) return 'appearance';
        if (str_starts_with($routeName, 'admin.leads')) return 'leads';
        if (str_starts_with($routeName, 'admin.settings')) return 'settings';
        if (str_starts_with($routeName, 'admin.instagram')) return 'instagram';
        if (str_starts_with($routeName, 'admin.users')) return 'users';

        return null;
    }
}
