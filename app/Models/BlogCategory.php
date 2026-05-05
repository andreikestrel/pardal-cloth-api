<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'color'];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
