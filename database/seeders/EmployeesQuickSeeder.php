<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeesQuickSeeder extends Seeder
{
    public function run(): void
    {
        // Crea 101 empleados: Empleado 001 ... Empleado 101
        for ($i = 1; $i <= 101; $i++) {
            Employee::create([
                'name'        => 'Empleado ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                // Genero un código estable y único tipo E00001, E00002, ...
                'employee_no' => 'E' . str_pad($i, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
