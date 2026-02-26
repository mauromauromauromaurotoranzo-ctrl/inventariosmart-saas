<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantLoginController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        $tenant = app('tenant');
        
        return view('auth.tenant-login', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $tenant = app('tenant');
        
        // Buscar usuario en la BD del tenant
        $user = \DB::connection('tenant')
            ->table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son válidas.'],
            ]);
        }

        // Crear token Sanctum
        $token = $this->createToken($user);

        // Guardar en sesión si es web
        if (!$request->expectsJson()) {
            session(['tenant_user_id' => $user->id]);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Revocar token
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        session()->forget('tenant_user_id');

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada',
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $tenant = app('tenant');

        return response()->json([
            'user' => $user,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'rubro' => $tenant->rubro,
                'plan' => $tenant->plan,
            ],
            'subscription' => [
                'on_trial' => $tenant->isOnTrial(),
                'trial_ends_at' => $tenant->trial_ends_at,
                'subscribed' => $tenant->subscribed_at !== null,
            ],
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        \DB::connection('tenant')
            ->table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada',
        ]);
    }

    /**
     * Solicitar reset de contraseña
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $tenant = app('tenant');
        
        $user = \DB::connection('tenant')
            ->table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            // No revelar si el email existe
            return response()->json([
                'success' => true,
                'message' => 'Si el email existe, recibirás instrucciones.',
            ]);
        }

        // Generar token
        $token = \Str::random(64);
        
        \DB::connection('tenant')
            ->table('password_resets')
            ->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

        // Enviar email (TODO: implementar)
        // dispatch(new SendPasswordReset($user, $token, $tenant));

        return response()->json([
            'success' => true,
            'message' => 'Si el email existe, recibirás instrucciones.',
        ]);
    }

    /**
     * Crear token de acceso
     */
    private function createToken($user): string
    {
        // Implementación simple - en producción usar Sanctum
        return hash('sha256', $user->id . time() . \Str::random(32));
    }
}
