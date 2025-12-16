<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class MailSettingsController extends Controller
{
    public function edit()
    {
        $settings = MailSetting::first();
        return view('admin.mail_settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'host' => ['required','string'],
            'port' => ['required','integer'],
            'username' => ['required','email'],
            'password' => ['nullable','string'],
            'encryption' => ['nullable','string'],
            'from_address' => ['required','email'],
            'from_name' => ['nullable','string'],
            'enabled' => ['nullable','boolean'],
        ]);

        $settings = MailSetting::first() ?? new MailSetting();
        $settings->host = $data['host'];
        $settings->port = $data['port'];
        $settings->username = $data['username'];
        if (! empty($data['password'])) {
            $settings->password = $data['password'];
        }
        $settings->encryption = $data['encryption'] ?? 'tls';
        $settings->from_address = $data['from_address'];
        $settings->from_name = $data['from_name'] ?? $data['from_address'];
        $settings->enabled = ! empty($data['enabled']);
        $settings->save();

        return back()->with('ok','Configuración SMTP guardada.');
    }

    public function sendTest(Request $request)
    {
        $settings = MailSetting::first();
        if (! $settings || ! $settings->enabled) {
            return back()->with('ok', 'SMTP no configurado o deshabilitado.');
        }

        // Aplicar configuración dinámica
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port,
            'encryption' => $settings->encryption,
            'username' => $settings->username,
            'password' => $settings->getDecryptedPassword(),
            'timeout' => null,
        ]);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name);

        $to = $request->input('test_to') ?: $settings->from_address;

        try {
            Mail::raw('Prueba SMTP desde el sistema. Si recibes este correo, la configuración funciona.', function ($m) use ($to, $settings) {
                $m->to($to)->from($settings->from_address, $settings->from_name)->subject('Prueba SMTP');
            });
            return back()->with('ok', 'Correo de prueba enviado a ' . $to);
        } catch (\Throwable $ex) {
            return back()->with('ok', 'Error enviando correo de prueba: ' . $ex->getMessage());
        }
    }
}
