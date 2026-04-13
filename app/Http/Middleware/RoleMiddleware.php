<?php
// ============================================================
// app/Http/Middleware/RoleMiddleware.php
// Protège les routes selon le rôle — utiliser dans routes/web.php
// Usage: Route::middleware(['auth', 'role:admin'])
//        Route::middleware(['auth', 'role:admin,chef_projet'])
// ============================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->role, $roles)) {
            // Retourne une réponse Inertia 403 ou redirige
            if ($request->header('X-Inertia')) {
                return response()->json(['message' => 'Accès non autorisé.'], 403);
            }
            abort(403, 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }

        return $next($request);
    }
}
