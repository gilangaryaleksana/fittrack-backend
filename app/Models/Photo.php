<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['activity_id', 'user_id', 'url', 'caption'];

    public function activity() { return $this->belongsTo(Activity::class); }
    public function user() { return $this->belongsTo(User::class); }
}
