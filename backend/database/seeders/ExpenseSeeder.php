<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $household = Household::first();
        $admin = User::where('email', 'admin@planer.test')->first();
        $ana = User::where('email', 'ana@planer.test')->first();
        $marko = User::where('email', 'marko@planer.test')->first();

        $members = [$admin, $ana, $marko];

        $data = [
            ['title' => 'Struja - lipanj', 'category' => 'utilities', 'amount' => 90.00, 'payer' => $admin, 'days_ago' => 2],
            ['title' => 'Internet', 'category' => 'utilities', 'amount' => 30.00, 'payer' => $ana, 'days_ago' => 5],
            ['title' => 'Namirnice - Konzum', 'category' => 'groceries', 'amount' => 64.50, 'payer' => $marko, 'days_ago' => 1],
            ['title' => 'Sredstva za čišćenje', 'category' => 'household', 'amount' => 22.30, 'payer' => $ana, 'days_ago' => 7],
            ['title' => 'Pizza - zajednička večera', 'category' => 'food', 'amount' => 48.00, 'payer' => $admin, 'days_ago' => 3],
        ];

        foreach ($data as $row) {
            $expense = Expense::firstOrCreate(
                ['household_id' => $household->id, 'title' => $row['title']],
                [
                    'paid_by_user_id' => $row['payer']->id,
                    'description' => null,
                    'amount' => $row['amount'],
                    'category' => $row['category'],
                    'expense_date' => Carbon::now()->subDays($row['days_ago']),
                ]
            );

            if ($expense->shares()->count() === 0) {
                $share = round($row['amount'] / count($members), 2);

                foreach ($members as $member) {
                    ExpenseShare::create([
                        'expense_id' => $expense->id,
                        'user_id' => $member->id,
                        'amount' => $share,
                        'is_settled' => $member->id === $row['payer']->id,
                    ]);
                }
            }
        }
    }
}
