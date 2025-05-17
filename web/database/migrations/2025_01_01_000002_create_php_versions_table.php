<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('php_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->boolean('default')->default(false);
            $table->string('binary_path')->nullable();
            $table->string('fpm_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('php_versions');
    }
};
