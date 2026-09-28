<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityStream extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'activity_id', 'sequence', 'latitude', 'longitude',
        'elevation', 'recorded_at', 'heartrate', 'cadence',
    ];

    protected $casts = ['recorded_at' => 'datetime'];

    public function activity() { return $this->belongsTo(Activity::class); }
}
