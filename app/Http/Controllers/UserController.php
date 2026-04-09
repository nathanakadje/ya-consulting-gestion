<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    /**
     * Mettre à jour le thème de l'utilisateur connecté.
     * Appelé par le toggle dans AppLayout.vue
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        $request->user()->update([
            'theme' => $request->theme,
        ]);

        // Pas de redirection — Inertia gère ça avec preserveState: true
        return back();
    }
}
