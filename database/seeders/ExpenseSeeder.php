<?php

namespace Database\Seeders;

use App\Domain\Finance\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $baseMonth = now()->startOfMonth()->subMonth();

        $expenses = [
            [
                'category' => 'rent',
                'amount' => 3200,
                'reference_month' => $baseMonth,
                'paid_at' => $baseMonth->copy()->addDays(3),
                'notes' => 'Aluguel da sala e condomínio.',
                'is_recurring' => true,
            ],
            [
                'category' => 'utilities',
                'amount' => 640,
                'reference_month' => $baseMonth,
                'paid_at' => $baseMonth->copy()->addDays(8),
                'notes' => 'Energia, água e internet.',
                'is_recurring' => true,
            ],
            [
                'category' => 'marketing',
                'amount' => 980,
                'reference_month' => $baseMonth,
                'paid_at' => $baseMonth->copy()->addDays(12),
                'notes' => 'Impulsionamento no Instagram para campanha pós-verão.',
                'is_recurring' => false,
            ],
        ];

        foreach ($expenses as $expense) {
            Expense::updateOrCreate(
                [
                    'category' => $expense['category'],
                    'reference_month' => $expense['reference_month'],
                ],
                $expense
            );
        }

        $this->command?->info('Despesas operacionais registradas.');
    }
}
