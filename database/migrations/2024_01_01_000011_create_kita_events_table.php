<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kita_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kita_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->date('end_date')->nullable();
            // SCHLIESSTAG | KURZE_ZEITEN | FORTBILDUNG | INFO
            $table->string('event_type', 20)->default('SCHLIESSTAG');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('start_time', 5)->nullable(); // HH:MM
            $table->string('end_time', 5)->nullable();   // HH:MM
            $table->timestamps();

            $table->index(['kita_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kita_events');
    }
};
