<?php

namespace Database\Seeders;

use App\Models\KTRLOption;
use App\Models\PhpVersion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $username = 'admin';
        $pass = 'test';
        $email = 'admin@ktrl.local';
        $fqdn = 'ktrl.local';
        $port = '8200';

        $user = User::create([
            'name' => 'KTRL Admin',
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($pass)
        ]);

        $team = Team::create([
            'name' => 'KTRL Admin Team',
            'owner_id' => $user->id,
            'slug' => 'ktrl-admin-team'
        ]);

        $team->members()->attach($user);

        PhpVersion::create([
            'version' => '8.4',
            'installed' => true,
            'default' => true,
            'binary_path' => '',
            'fpm_path' => '',
        ]);

        /** Create KTRLOptions */
        KTRLOption::create([
            'fqdn'         => $fqdn,
            'port'         => $port,
            'ktrl_version' => 'dev'
        ]);
    }
}
