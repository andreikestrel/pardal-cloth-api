<?php

namespace App\Models;

use Spatie\Tags\Tag as SpatieTag;

class Tag extends SpatieTag
{
    // Expose color and link_url through the standard fillable/guarded mechanism.
    // The parent class uses $guarded = [] so all columns are mass-assignable already.
}
