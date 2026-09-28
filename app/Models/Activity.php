<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id', 'activity_type_id', 'title', 'description', 'distance',
        'moving_time', 'elapsed_time', 'elevation_gain', 'start_date',
        'start_lat', 'start_lng', 'end_lat', 'end_lng',
        'average_speed', 'max_speed', 'calories', 'visibility',
    ];

    protected $casts = ['start_date' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function activityType() { return $this->belongsTo(ActivityType::class); }
    public function streams() { return $this->hasMany(ActivityStream::class)->orderBy('sequence'); }
    public function kudos() { return $this->hasMany(Kudo::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function photos() { return $this->hasMany(Photo::class); }
    public function segmentEfforts() { return $this->hasMany(SegmentEffort::class); }
}
