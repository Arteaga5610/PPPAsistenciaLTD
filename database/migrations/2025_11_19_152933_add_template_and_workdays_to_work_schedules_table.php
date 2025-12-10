<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            // Relación opcional a plantilla
            $table->unsignedBigInteger('schedule_template_id')->nullable()->after('id');

            // Días laborables específicos de este empleado (JSON [1,2,3,...])
            $table->json('work_days')->nullable()->after('exit_plus');

            $table->foreign('schedule_template_id')
                  ->references('id')
                  ->on('schedule_templates')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropForeign(['schedule_template_id']);
            $table->dropColumn(['schedule_template_id', 'work_days']);
        });
    }
};
