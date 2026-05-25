<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reaction_id')->constrained('reactions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
            $table->index(['post_id', 'reaction_id'], 'post_reactions_post_reaction_idx');
            $table->index(['user_id', 'post_id'], 'post_reactions_user_post_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};
