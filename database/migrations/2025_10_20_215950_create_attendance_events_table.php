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
          Schema::create('attendance_events', function (Blueprint $table) {
              $table->id();

              $table->string('device_ip', 45)->nullable();      // IPv4/IPv6
              $table->string('employee_no', 64)->nullable();    // E.g. 'EABC123'
              $table->string('event_type', 64)->nullable();     // access_granted / access_denied / face_match / fingerprint / etc
              $table->string('method', 32)->nullable();         // face|fp|card|pw|mixed
              $table->string('result', 32)->nullable();         // success|fail|denied_xxx
              $table->dateTime('event_time')->nullable();       // del equipo

              $table->text('raw_payload');                      // XML/JSON completo
              $table->timestamps();

              $table->index(['employee_no']);
              $table->index(['event_time']);
              $table->index(['device_ip']);
          });
      }

      public function down(): void
      {
          Schema::dropIfExists('attendance_events');
      }
  };