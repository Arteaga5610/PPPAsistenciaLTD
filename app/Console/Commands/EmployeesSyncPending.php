<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\HikvisionClient;

class EmployeesSyncPending extends Command
{
    protected $signature = 'employees:sync-pending';
    protected $description = 'Sincroniza con Hikvision todos los empleados sin synced_at';

    public function handle(HikvisionClient $hik): int
    {
        $pending = Employee::whereNull('synced_at')->get();
        $ok=0; $fail=0;

        foreach ($pending as $e) {
            $res = false;
            try { $res = $hik->pushUser($e); } catch (\Throwable $ex) {}
            if ($res) { $e->synced_at = now(); $e->saveQuietly(); $ok++; }
            else { $fail++; }
        }

        $this->info("Sincronizados: $ok, fallidos: $fail");
        return self::SUCCESS;
    }
}
