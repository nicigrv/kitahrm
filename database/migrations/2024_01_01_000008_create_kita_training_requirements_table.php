<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kita_training_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kita_id')->constrained('kitas')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('training_categories')->cascadeOnDelete();
            $table->integer('min_count')->default(1);
            $table->timestamps();
            $table->unique(['kita_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kita_training_requirements');
    }
};
