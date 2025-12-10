<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta si usarás auth
    }

    public function rules(): array
    {
    return [
        'name'   => ['required','string','max:128'],
        'gender' => ['required', Rule::in(['male','female','unknown'])],
        'hire_date'  => ['nullable','date'],   // <--- NUEVO
        'hourly_rate' => ['required','numeric','min:0'],
        'entry_time'       => ['nullable','date_format:H:i'],
        'exit_time'        => ['nullable','date_format:H:i'],
        'entry_early_min'  => ['nullable','integer','min:0','max:240'],
        'entry_late_min'   => ['nullable','integer','min:0','max:240'],
        'exit_early_min'   => ['nullable','integer','min:0','max:240'],
        'exit_late_min'    => ['nullable','integer','min:0','max:240'],
    ];
    }

    public function messages(): array
    {
        return [
            'employee_no.unique' => 'El employee_no ya existe.',
            'groups.max'         => 'Máximo 4 grupos.',
        ];
    }
}
