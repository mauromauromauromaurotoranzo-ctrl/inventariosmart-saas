<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRubro
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$rubros  Rubros permitidos
     */
    public function handle(Request $request, Closure $next, string ...$rubros): Response
    {
        // Obtener tenant del contenedor
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant no identificado',
                'message' => 'No se pudo verificar el rubro'
            ], 500);
        }
        
        // Verificar si el rubro del tenant está en la lista permitida
        if (!in_array($tenant->rubro, $rubros)) {
            $rubrosList = implode(', ', $rubros);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Función no disponible',
                    'message' => "Esta función solo está disponible para: {$rubrosList}",
                    'your_rubro' => $tenant->rubro,
                ], 403);
            }
            
            return response()->view('errors.rubro-not-allowed', [
                'message' => "Esta función solo está disponible para: {$rubrosList}",
                'your_rubro' => $tenant->rubro,
                'allowed_rubros' => $rubros,
            ], 403);
        }
        
        return $next($request);
    }
}
