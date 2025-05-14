<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Administrator',
            'email' => 'you@example.com',
            'password' => Hash::make('you@example.com')
        ]);

        $team = Team::create([
            'name' => 'The Admin Team',
            'owner_id' => $user->id,
            'slug' => 'the-admin-team'
        ]);

        $team->members()->attach($user);
    }
}
