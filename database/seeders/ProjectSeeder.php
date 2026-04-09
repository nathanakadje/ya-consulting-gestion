<?php

namespace Database\Seeders;

use App\Models\{Client, Expense, ExpenseCategory, Project, User};
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin     = User::where('role', 'admin')->first();
        $chef      = User::where('role', 'project_manager')->first();
        $clients   = Client::all();
        $categories = ExpenseCategory::all();

        $projects = [
            [
                'name'             => 'Audit Système Informatique SODECI',
                'reference'        => 'YA-2024-001',
                'description'      => 'Audit complet du système d\'information et recommandations de sécurité.',
                'client_id'        => $clients[1]->id,
                'budget'           => 12_500_000,
                'start_date'       => '2024-01-15',
                'end_date_planned' => '2024-06-30',
                'status'           => 'termine',
                'end_date_actual'  => '2024-06-25',
            ],
            [
                'name'             => 'Déploiement Réseau Fibre Optique RTI',
                'reference'        => 'YA-2024-002',
                'description'      => 'Installation et configuration du réseau fibre optique pour les studios de la RTI.',
                'client_id'        => $clients[4]->id,
                'budget'           => 45_000_000,
                'start_date'       => '2024-03-01',
                'end_date_planned' => '2024-12-31',
                'status'           => 'en_cours',
            ],
            [
                'name'             => 'Étude de faisabilité Ministère Infras.',
                'reference'        => 'YA-2024-003',
                'description'      => 'Étude technico-économique pour la modernisation des infrastructures routières.',
                'client_id'        => $clients[0]->id,
                'budget'           => 8_000_000,
                'start_date'       => '2024-02-01',
                'end_date_planned' => '2024-04-30',
                'status'           => 'termine',
                'end_date_actual'  => '2024-05-02',
            ],
            [
                'name'             => 'Consulting Digital Orange CI',
                'reference'        => 'YA-2024-004',
                'description'      => 'Accompagnement transformation digitale et conduite du changement.',
                'client_id'        => $clients[2]->id,
                'budget'           => 22_000_000,
                'start_date'       => '2024-04-15',
                'end_date_planned' => '2025-04-14',
                'status'           => 'en_cours',
            ],
            [
                'name'             => 'Analyse Risques NSIA',
                'reference'        => 'YA-2024-005',
                'description'      => 'Analyse et cartographie des risques opérationnels.',
                'client_id'        => $clients[3]->id,
                'budget'           => 6_500_000,
                'start_date'       => '2024-05-01',
                'end_date_planned' => '2024-08-31',
                'status'           => 'en_pause',
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::create(array_merge($data, [
                'created_by'       => $admin->id,
                'project_lead_id'  => $chef->id,
                'budget_main_oeuvre' => $data['budget'] * 0.4,
                'budget_materiel'    => $data['budget'] * 0.3,
                'budget_transport'   => $data['budget'] * 0.1,
                'budget_autres'      => $data['budget'] * 0.2,
            ]));

            // Créer des dépenses réalistes pour chaque projet
            $this->createExpenses($project, $categories, $admin, $chef);
        }
    }

    private function createExpenses(Project $project, $categories, User $admin, User $chef): void
    {
        $expenseData = [
            ['desc' => 'Honoraires consultant principal',   'ratio' => 0.15, 'cat' => 'Main d\'œuvre'],
            ['desc' => 'Achat équipements informatiques',   'ratio' => 0.12, 'cat' => 'Achat matériel'],
            ['desc' => 'Sous-traitance expertise réseau',   'ratio' => 0.10, 'cat' => 'Sous-traitance'],
            ['desc' => 'Frais de déplacement terrain',      'ratio' => 0.04, 'cat' => 'Déplacement'],
            ['desc' => 'Communication et documentation',    'ratio' => 0.03, 'cat' => 'Communication'],
            ['desc' => 'TVA et droits de timbre',           'ratio' => 0.05, 'cat' => 'Impôts / taxes'],
        ];

        $startDate = Carbon::parse($project->start_date);
        $offset    = 0;

        foreach ($expenseData as $item) {
            $cat = $categories->firstWhere('name', $item['cat']);
            if (!$cat) continue;

            Expense::create([
                'project_id'   => $project->id,
                'category_id'  => $cat->id,
                'created_by'   => ($offset % 2 === 0) ? $admin->id : $chef->id,
                'description'  => $item['desc'],
                'amount'       => round($project->budget * $item['ratio'], 0),
                'expense_date' => $startDate->copy()->addWeeks($offset + 1),
                'status'       => 'validated',
            ]);
            $offset++;
        }
    }
}
