<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kita_closing_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kita_id')->constrained('kitas')->cascadeOnDelete();
            $table->date('date');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->unique(['kita_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kita_closing_days');
    }
};
