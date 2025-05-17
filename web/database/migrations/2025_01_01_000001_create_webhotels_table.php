<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhotels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Unnamed');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('php_version')->default('8.4');
            $table->unsignedInteger('port')->unique();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhotels');
    }
};
