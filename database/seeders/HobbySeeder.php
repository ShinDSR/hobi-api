<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hobby;
use Illuminate\Database\Seeder;

class HobbySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        // 3 hobby yang sama untuk semua user
        $commonHobbies = ['Reading', 'Cycling', 'Swimming'];

        foreach ($users as $user) {
            // Tambahkan 3 hobby yang sama
            foreach ($commonHobbies as $hobbyName) {
                Hobby::create([
                    'user_id' => $user->id,
                    'name' => $hobbyName,
                ]);
            }

            // Tambahkan 2 hobby yang berbeda (unik/random untuk tiap user)
            Hobby::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
