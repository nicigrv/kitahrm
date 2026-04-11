<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitas', function (Blueprint $table) {
            $table->decimal('target_weekly_hours', 6, 1)->default(0.0)->after('min_skilled_staff');
        });
    }

    public function down(): void
    {
        Schema::table('kitas', function (Blueprint $table) {
            $table->dropColumn('target_weekly_hours');
        });
    }
};
