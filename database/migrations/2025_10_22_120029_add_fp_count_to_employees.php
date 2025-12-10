<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
          public function up(): void
      {
          Schema::table('employees', function (\Illuminate\Database\Schema\Blueprint $table) {
              if (!Schema::hasColumn('employees', 'fp_count')) {
                  $table->unsignedSmallInteger('fp_count')->default(0)->after('door_right');
              }
              if (!Schema::hasColumn('employees', 'has_fp')) {
                  $table->boolean('has_fp')->default(false)->after('fp_count');
              }
          });
      }

      public function down(): void
      {
          Schema::table('employees', function (\Illuminate\Database\Schema\Blueprint $table) {
              if (Schema::hasColumn('employees', 'has_fp')) {
                  $table->dropColumn('has_fp');
              }
              if (Schema::hasColumn('employees', 'fp_count')) {
                  $table->dropColumn('fp_count');
              }
          });
      }

};
