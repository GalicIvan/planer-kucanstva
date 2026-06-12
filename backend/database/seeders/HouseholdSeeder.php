<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class HouseholdSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@planer.test')->first();
        $ana = User::where('email', 'ana@planer.test')->first();
        $marko = User::where('email', 'marko@planer.test')->first();
        $superAdmin = User::where('email', 'superadmin@planer.test')->first();

        $household = Household::firstOrCreate(
            ['name' => 'Stan u Zagrebu'],
            [
                'description' => 'Zajednički stan - Ana, Marko i Admin',
                'created_by' => $admin->id,
            ]
        );

        foreach ([
            [$admin->id, 'admin'],
            [$ana->id, 'member'],
            [$marko->id, 'member'],
            [$superAdmin->id, 'admin'],
        ] as [$userId, $memberRole]) {
            HouseholdMember::firstOrCreate(
                ['household_id' => $household->id, 'user_id' => $userId],
                ['member_role' => $memberRole, 'joined_at' => now()]
            );
        }
    }
}
