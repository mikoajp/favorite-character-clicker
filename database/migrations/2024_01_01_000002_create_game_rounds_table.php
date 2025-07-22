<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->integer('round_number');
            $table->string('character1_id');
            $table->string('character2_id');
            $table->string('selected_character_id');
            $table->string('eliminated_character_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};