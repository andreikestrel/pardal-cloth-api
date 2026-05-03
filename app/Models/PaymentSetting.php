<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentSetting extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'gateway',
        'credentials',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active'     => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function getDecryptedCredentials(): array
    {
        return json_decode(Crypt::decrypt($this->credentials), true);
    }

    public function setCredentialsAttribute(array $value): void
    {
        $this->attributes['credentials'] = Crypt::encrypt(json_encode($value));
    }
}
