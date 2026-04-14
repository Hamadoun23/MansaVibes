<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventTailleur
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'tailleur') {
            abort(403, __('messages.tailor_forbidden'));
        }

        return $next($request);
    }
}
