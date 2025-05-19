<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ftp_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('webhotel_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->string('username', 32)->unique();
            $table->string('password', 64); // Use MD5 or bcrypt depending on ProFTPd config
            $table->string('homedir', 255);
            $table->string('shell', 16)->default('/sbin/nologin');
            $table->integer('uid')->default(33); // Usually www-data
            $table->integer('gid')->default(33);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ftp_users');
    }
};
