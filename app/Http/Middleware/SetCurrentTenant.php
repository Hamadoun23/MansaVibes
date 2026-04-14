<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->tenant !== null) {
            CurrentTenant::set($user->tenant);
        } else {
            CurrentTenant::clear();
        }

        try {
            return $next($request);
        } finally {
            CurrentTenant::clear();
        }
    }
}
