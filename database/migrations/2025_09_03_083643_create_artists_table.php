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
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lang')->default("uk");
            $table->text('biography')->nullable();
            $table->string('mbid')->nullable();//lastfm
            $table->string('spotifyid')->nullable();
            $table->json('spotify_albums')->nullable();
            $table->json('similar')->nullable();
            $table->json('topalbums')->nullable();
            $table->json('youtube_full_album')->nullable();
            $table->json('youtube_live')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
