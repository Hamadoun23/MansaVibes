<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $singleId = config('mansavibes.single_tenant_id');

        if ($singleId !== null && $singleId !== '') {
            $tenant = Tenant::query()->find((int) $singleId);
            if ($tenant !== null) {
                CurrentTenant::set($tenant);
            } else {
                CurrentTenant::clear();
            }
        } else {
            $user = $request->user();
            if ($user !== null && $user->tenant !== null) {
                CurrentTenant::set($user->tenant);
            } else {
                CurrentTenant::clear();
            }
        }

        try {
            return $next($request);
        } finally {
            CurrentTenant::clear();
        }
    }
}
