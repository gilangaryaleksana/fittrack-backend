<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gear extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'name', 'type', 'total_distance', 'is_retired'];

    protected $casts = ['is_retired' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
