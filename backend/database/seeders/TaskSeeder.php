<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $household = Household::first();
        $admin = User::where('email', 'admin@planer.test')->first();
        $ana = User::where('email', 'ana@planer.test')->first();
        $marko = User::where('email', 'marko@planer.test')->first();

        $tasks = [
            ['title' => 'Iznijeti smeće', 'assigned' => $marko, 'status' => 'pending', 'due' => 1],
            ['title' => 'Usisati dnevni boravak', 'assigned' => $ana, 'status' => 'pending', 'due' => 2],
            ['title' => 'Platiti račun za internet', 'assigned' => $admin, 'status' => 'done', 'due' => -1],
            ['title' => 'Oprati posuđe', 'assigned' => $marko, 'status' => 'done', 'due' => -2],
            ['title' => 'Kupiti sredstvo za pranje suđa', 'assigned' => $ana, 'status' => 'pending', 'due' => 3],
        ];

        foreach ($tasks as $t) {
            Task::firstOrCreate(
                ['household_id' => $household->id, 'title' => $t['title']],
                [
                    'assigned_to_user_id' => $t['assigned']->id,
                    'created_by_user_id' => $admin->id,
                    'description' => null,
                    'status' => $t['status'],
                    'due_date' => Carbon::now()->addDays($t['due']),
                ]
            );
        }
    }
}
