<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('position', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('contract_type', ['UNBEFRISTET', 'BEFRISTET', 'MINIJOB', 'AUSBILDUNG', 'PRAKTIKUM', 'ELTERNZEIT'])->default('UNBEFRISTET');
            $table->decimal('weekly_hours', 4, 1)->default(39.0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('kita_id')->constrained('kitas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
