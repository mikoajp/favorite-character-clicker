<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('character_id');
            $table->string('character_name');
            $table->string('character_image')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'character_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_characters');
    }
};