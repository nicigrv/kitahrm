<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitas', function (Blueprint $table) {
            $table->integer('min_staff_total')->default(0)->after('min_first_aid');
            $table->integer('min_skilled_staff')->default(0)->after('min_staff_total');
            $table->text('notes')->nullable()->after('min_skilled_staff');
        });
    }

    public function down(): void
    {
        Schema::table('kitas', function (Blueprint $table) {
            $table->dropColumn(['min_staff_total', 'min_skilled_staff', 'notes']);
        });
    }
};
