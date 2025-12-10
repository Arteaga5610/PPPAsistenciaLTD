<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void {
        Schema::table('employees', function (Blueprint $t) {
            $t->time('entry_time')->nullable();
            $t->time('exit_time')->nullable();
            $t->smallInteger('entry_early_min')->default(15);
            $t->smallInteger('entry_late_min')->default(15);
            $t->smallInteger('exit_early_min')->default(10);
            $t->smallInteger('exit_late_min')->default(10);
        });
    }
    public function down(): void {
        Schema::table('employees', function (Blueprint $t) {
            $t->dropColumn([
                'entry_time','exit_time',
                'entry_early_min','entry_late_min',
                'exit_early_min','exit_late_min'
            ]);
        });
    }
};