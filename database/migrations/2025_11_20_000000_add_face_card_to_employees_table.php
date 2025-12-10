<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'has_face')) {
                $table->boolean('has_face')->default(false)->after('has_fp');
            }
            if (! Schema::hasColumn('employees', 'has_card')) {
                $table->boolean('has_card')->default(false)->after('has_face');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'has_card')) {
                $table->dropColumn('has_card');
            }
            if (Schema::hasColumn('employees', 'has_face')) {
                $table->dropColumn('has_face');
            }
        });
    }
};
