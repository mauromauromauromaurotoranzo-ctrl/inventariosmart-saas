<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRubro
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $rubro)
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            abort(403, 'No se encontró el tenant');
        }
        
        if ($tenant->rubro() !== $rubro) {
            abort(403, 'Esta función no está disponible para tu rubro');
        }
        
        return $next($request);
    }
}
