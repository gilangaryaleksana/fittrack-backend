<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'name', 'activity_type_id', 'distance', 'average_grade', 'elevation_gain',
        'start_lat', 'start_lng', 'end_lat', 'end_lng', 'city',
    ];

    public function activityType() { return $this->belongsTo(ActivityType::class); }
    public function efforts() { return $this->hasMany(SegmentEffort::class); }
}
