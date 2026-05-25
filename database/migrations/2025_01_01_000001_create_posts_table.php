<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->string('image')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['visibility', 'created_at']);
            $table->index(['user_id', 'created_at'], 'posts_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
