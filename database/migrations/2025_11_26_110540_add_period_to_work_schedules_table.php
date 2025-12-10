<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            // Fecha desde cuando es válido este horario
            $table->date('start_date')->nullable()->after('employee_no');
            // Fecha hasta cuando es válido (null = sigue vigente)
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
