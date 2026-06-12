<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShoppingItemSeeder extends Seeder
{
    public function run(): void
    {
        $household = Household::first();
        $ana = User::where('email', 'ana@planer.test')->first();
        $marko = User::where('email', 'marko@planer.test')->first();

        $items = [
            ['name' => 'Mlijeko', 'quantity' => 2, 'creator' => $ana, 'purchased' => false],
            ['name' => 'Kruh', 'quantity' => 1, 'creator' => $marko, 'purchased' => false],
            ['name' => 'Jaja', 'quantity' => 1, 'creator' => $ana, 'purchased' => true],
            ['name' => 'Toaletni papir', 'quantity' => 1, 'creator' => $marko, 'purchased' => false],
            ['name' => 'Kava', 'quantity' => 1, 'creator' => $ana, 'purchased' => false],
        ];

        foreach ($items as $item) {
            ShoppingItem::firstOrCreate(
                ['household_id' => $household->id, 'name' => $item['name']],
                [
                    'created_by_user_id' => $item['creator']->id,
                    'quantity' => $item['quantity'],
                    'is_purchased' => $item['purchased'],
                ]
            );
        }
    }
}
