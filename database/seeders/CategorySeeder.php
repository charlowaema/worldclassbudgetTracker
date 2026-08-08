<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            ['name' => 'Housing & Rent', 'icon' => 'home', 'color' => '#f97316'],
            ['name' => 'Groceries', 'icon' => 'shopping-cart', 'color' => '#22c55e'],
            ['name' => 'Transport', 'icon' => 'car', 'color' => '#3b82f6'],
            ['name' => 'Utilities', 'icon' => 'zap', 'color' => '#eab308'],
            ['name' => 'Dining Out', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['name' => 'Healthcare', 'icon' => 'heart-pulse', 'color' => '#ec4899'],
            ['name' => 'Entertainment', 'icon' => 'clapperboard', 'color' => '#a855f7'],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#14b8a6'],
            ['name' => 'Education', 'icon' => 'graduation-cap', 'color' => '#6366f1'],
            ['name' => 'Insurance', 'icon' => 'shield', 'color' => '#64748b'],
            ['name' => 'Debt & Loans', 'icon' => 'credit-card', 'color' => '#dc2626'],
            ['name' => 'Savings & Investments', 'icon' => 'piggy-bank', 'color' => '#059669'],
            ['name' => 'Other Expense', 'icon' => 'more-horizontal', 'color' => '#94a3b8'],
        ];

        $incomeCategories = [
            ['name' => 'Salary', 'icon' => 'wallet', 'color' => '#22c55e'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#3b82f6'],
            ['name' => 'Business', 'icon' => 'briefcase', 'color' => '#f97316'],
            ['name' => 'Investments', 'icon' => 'trending-up', 'color' => '#059669'],
            ['name' => 'Gifts', 'icon' => 'gift', 'color' => '#ec4899'],
            ['name' => 'Other Income', 'icon' => 'more-horizontal', 'color' => '#94a3b8'],
        ];

        foreach ($expenseCategories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name'], 'type' => 'expense', 'user_id' => null],
                ['icon' => $cat['icon'], 'color' => $cat['color'], 'is_default' => true]
            );
        }

        foreach ($incomeCategories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name'], 'type' => 'income', 'user_id' => null],
                ['icon' => $cat['icon'], 'color' => $cat['color'], 'is_default' => true]
            );
        }
    }
}
