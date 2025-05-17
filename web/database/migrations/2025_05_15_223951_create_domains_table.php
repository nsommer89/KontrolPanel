<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('webhotel_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->string('domain')->unique();
            $table->boolean('primary')->default(false);
            $table->boolean('ssl_enabled')->default(false);
            $table->string('cert_path')->nullable();
            $table->string('key_path')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
