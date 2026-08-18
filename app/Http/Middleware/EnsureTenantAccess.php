<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user !== null && $user->tenant_id !== null && $user->is_active, 403);

        // Controllers and policies must still scope every query by this tenant_id.
        $request->attributes->set('tenant_id', (int) $user->tenant_id);

        return $next($request);
    }
}
