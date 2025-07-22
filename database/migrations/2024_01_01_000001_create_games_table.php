<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable(); // For guest users
            $table->integer('total_characters');
            $table->integer('current_round')->default(1);
            $table->json('characters'); // Store character data
            $table->json('eliminated_characters')->nullable(); // Store eliminated characters
            $table->string('winner_character_id')->nullable();
            $table->string('status')->default('active'); // active, completed, abandoned
            $table->integer('score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};