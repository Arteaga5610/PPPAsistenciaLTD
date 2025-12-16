<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host')->default('smtp.gmail.com');
            $table->integer('port')->default(587);
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted
            $table->string('encryption')->default('tls');
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mail_settings');
    }
};
