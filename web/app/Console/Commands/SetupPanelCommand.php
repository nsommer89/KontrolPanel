<?php

namespace App\Console\Commands;

use App\Models\KTRLOption;
use App\Models\PhpVersion;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class SetupPanelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:setup {email} {user} {pass} {fqdn} {port} {ktrl_version} {php_version} {php_bin_path} {php_fpm_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the KontrolPanel initial admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $user = $this->argument('user');
            $pass = $this->argument('pass');
            $email = $this->argument('email');
            $fqdn = $this->argument('fqdn');
            $port = $this->argument('port');
            $ktrl_version = $this->argument('ktrl_version');
            $php_version = $this->argument('php_version');
            $php_bin_path = $this->argument('php_bin_path');
            $php_fpm_path = $this->argument('php_fpm_path');

            $user = User::create([
                'name' => 'KTRL Admin',
                'username' => $user,
                'email' => $email,
                'password' => Hash::make($pass)
            ]);

            $team = Team::create([
                'name' => 'KTRL Admin Team',
                'owner_id' => $user->id,
                'slug' => 'ktrl-admin-team'
            ]);

            $team->members()->attach($user);

            /** Create KTRLOptions */
            $options = KTRLOption::create([
                'fqdn' => $fqdn,
                'port' => $port,
                'ktrl_version' => $ktrl_version,
            ]);


            // Create the currently installed PHP version
            PhpVersion::create([
                'version' => $php_version,
                'installed' => true,
                'default' => true,
                'binary_path' => $php_bin_path,
                'fpm_path' => $php_fpm_path
            ]);

            $this->info("KontrolPanel settings was created successfully.");

        } catch (Throwable $e) {
            $this->info($e->getMessage());
            Log::info($e->getMessage(), ($e->getTrace()));
        }
    }
}
