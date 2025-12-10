<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\HikvisionClient;

class EmployeesSyncAll extends Command
{
    protected $signature = 'employees:sync-all {--force : Ignorar synced_at y enviar todos}';
    protected $description = 'Empuja todos los empleados al dispositivo Hikvision (diagnóstico).';

    public function handle(HikvisionClient $hik)
    {
        $q = Employee::query();
        if (!$this->option('force')) {
            $q->whereNull('synced_at');
        }
        $emps = $q->orderBy('id')->get();

        $ok = 0; $fail = 0;
        foreach ($emps as $e) {
            $res = $hik->pushUser($e);
            $this->line(($res ? '[OK]   ' : '[FAIL] ').$e->employee_no.' '.$e->name);
            $res ? $ok++ : $fail++;
        }
        $this->info("Listo. OK={$ok}, FAIL={$fail}");
        return Command::SUCCESS;
    }
}
