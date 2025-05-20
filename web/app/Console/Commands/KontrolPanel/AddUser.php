<?php

namespace App\Console\Commands\KontrolPanel;

use App\Models\PhpVersion;
use App\Models\Webhotel;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ktrl:adduser {username} {password} {team_id} {name} {php_version} {port} {enabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new system user with home directory and required directories.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $teamId = $this->argument('team_id');
        $name = $this->argument('name');
        $phpVersion = $this->argument('php_version');
        $port = $this->argument('port');
        $enabled = $this->argument('enabled');

        if (!$username || !$password) {
            $this->error('❌ Username and password must be provided.');
            $this->line('Usage: php artisan system:add-user --username=foo --password=bar');
            return 1;
        }

        // Check if user already exists
        $checkUser = new Process(['id', $username]);
        $checkUser->run();

        if ($checkUser->isSuccessful()) {
            $this->error("❌ User '{$username}' already exists.");
            return 1;
        }

        // Create user
        $this->info("➕ Creating system user '{$username}'...");
        Process::fromShellCommandline("adduser --gecos \"\" --disabled-password {$username}")->mustRun();
        Process::fromShellCommandline("echo \"{$username}:{$password}\" | chpasswd")->mustRun();

        // Directories
        $base = "/home/{$username}";
        $this->info("📁 Creating directories...");
        foreach (['web', 'logs', 'backups', 'ssl', 'ssl/private'] as $dir) {
            Process::fromShellCommandline("mkdir -p {$base}/{$dir}")->mustRun();
        }

        // Get UID and GID
        $uid = trim(shell_exec("id -u {$username}"));
        $gid = trim(shell_exec("id -g {$username}"));

        // Set ownership and perms
        Process::fromShellCommandline("chown -R {$uid}:{$gid} {$base}")->mustRun();
        Process::fromShellCommandline("chmod 755 {$base}")->mustRun();

        $this->info("✅ User '{$username}' created.");
        $this->line("🔐 UID: {$uid}");
        $this->line("🔐 GID: {$gid}");

        $phpVersion = PhpVersion::where('version', $phpVersion)->first();
        if (!$phpVersion) {
            $this->error("❌ PHP version '{$phpVersion}' not found.");
            return 1;
        }

        if ($uid && $gid) {
            Webhotel::create([
                'name' => $name,
                'system_user' => $username,
                'system_user_uid' => $uid,
                'system_user_gid' => $gid,
                'team_id' => $teamId,
                'php_version' => $phpVersion,
                'port' => $port,
                'enabled' => $enabled,
            ]);
            $this->info("✅ Webhotel '{$name}' created.");
        } else {
            $this->error("❌ Failed to create webhotel '{$name}'.");
            return 1;
        }
        $this->info("✅ Webhotel '{$name}' created with PHP version '{$phpVersion}' on port '{$port}'.");

        return 0;
    }
}
