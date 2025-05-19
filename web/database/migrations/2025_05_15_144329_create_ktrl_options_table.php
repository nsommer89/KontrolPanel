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
        Schema::create('ktrl_options', function (Blueprint $table) {
            $table->id();
            $table->string('fqdn');
            $table->integer('port');
            $table->string('ktrl_version');
            $table->integer('pma_port')->default(8081);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ktrl_options');
    }
};
