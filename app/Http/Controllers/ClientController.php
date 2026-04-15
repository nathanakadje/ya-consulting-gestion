<?php
// ============================================================
// app/Http/Controllers/ClientController.php
// ============================================================
namespace App\Http\Controllers;

use App\Models\{ActivityLog, Client};
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Création rapide depuis le modal dans Projects/Form.vue
     * Retourne le client créé en JSON pour que Vue l'ajoute
     * directement dans la liste et le sélectionne.
     *
     * Route: POST /clients/quick-create
     */
    public function quickCreate(Request $request)
    {
        $this->authorizeRole(['admin', 'chef_projet']);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'contact_person' => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:500',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
        ], [
            'name.required' => 'Le nom du client est obligatoire.',
            'email.email'   => 'Format d\'email invalide.',
        ]);

        $validated['country'] = $validated['country'] ?? "Côte d'Ivoire";

        $client = Client::create($validated);

        ActivityLog::log('created_client', $client, "Client \"{$client->name}\" créé (création rapide)");

        // Retourner le client créé en JSON
        // Vue l'ajoute dans la liste locale et le sélectionne automatiquement
        return response()->json([
            'success' => true,
            'client'  => [
                'id'             => $client->id,
                'name'           => $client->name,
                'email'          => $client->email,
                'phone'          => $client->phone,
                'contact_person' => $client->contact_person,
                'city'           => $client->city,
                'country'        => $client->country,
            ],
            'message' => "Client \"{$client->name}\" créé avec succès.",
        ], 201);
    }

    /**
     * Liste complète pour une éventuelle page /clients
     * (non utilisée dans le MVP mais prête pour plus tard)
     */
    public function index()
    {
        $clients = Client::withCount('projects')
            ->orderBy('name')
            ->get();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    // ── Helper ─────────────────────────────────────────────
    private function authorizeRole(array $roles): void
    {
        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
