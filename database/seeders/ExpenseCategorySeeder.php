<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Achat matériel',   'color' => '#3b82f6', 'icon' => 'inventory_2',    'is_system' => true],
            ['name' => 'Sous-traitance',   'color' => '#8b5cf6', 'icon' => 'engineering',    'is_system' => true],
            ['name' => 'Déplacement',      'color' => '#f59e0b', 'icon' => 'directions_car',  'is_system' => true],
            ['name' => 'Communication',    'color' => '#10b981', 'icon' => 'campaign',         'is_system' => true],
            ['name' => 'Impôts / taxes',   'color' => '#ef4444', 'icon' => 'account_balance', 'is_system' => true],
            ['name' => 'Main d\'œuvre',    'color' => '#0ea5e9', 'icon' => 'groups',           'is_system' => true],
            ['name' => 'Transport',        'color' => '#f97316', 'icon' => 'local_shipping',   'is_system' => true],
            ['name' => 'Divers',           'color' => '#6b7280', 'icon' => 'category',         'is_system' => true],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::create($cat);
        }
    }
}
