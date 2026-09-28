<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'icon'];

    public function activities() { return $this->hasMany(Activity::class); }
    public function segments() { return $this->hasMany(Segment::class); }
}
