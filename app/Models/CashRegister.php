<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'location', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function sessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }

    public function activeSession(): ?CashSession
    {
        return $this->sessions()->whereNull('closed_at')->latest('opened_at')->first();
    }
}
