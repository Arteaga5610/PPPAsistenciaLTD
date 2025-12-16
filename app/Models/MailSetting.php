<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MailSetting extends Model
{
    protected $fillable = [
        'host','port','username','password','encryption','from_address','from_name','enabled'
    ];

    protected $casts = [
        'port' => 'integer',
        'enabled' => 'boolean',
    ];

    // Store encrypted
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    // Return decrypted
    public function getDecryptedPassword()
    {
        if (empty($this->password)) return null;
        try {
            return Crypt::decryptString($this->password);
        } catch (\Throwable $ex) {
            return null;
        }
    }
}
