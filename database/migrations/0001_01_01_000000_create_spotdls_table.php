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
        Schema::create('spotdls', function (Blueprint $table) {
            $table->id();
            $table->string('artist');
            $table->string('album');
            $table->string('path');
            $table->integer('nbtracks')->default(0);
            $table->boolean('todo')->default(false);
            $table->boolean('done')->default(false);
            $table->boolean('avoid')->default(false);
            $table->string('spotifyurl')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotdls');
    }
};
