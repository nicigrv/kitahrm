<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = session('user_role');

        if (!$userRole || !in_array($userRole, $roles)) {
            abort(403, 'Zugriff verweigert. Sie haben nicht die erforderliche Berechtigung.');
        }

        return $next($request);
    }
}
