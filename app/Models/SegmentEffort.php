<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SegmentEffort extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'segment_id', 'activity_id', 'user_id', 'elapsed_time',
        'moving_time', 'start_date', 'kom_rank',
    ];

    protected $casts = ['start_date' => 'datetime'];

    public function segment() { return $this->belongsTo(Segment::class); }
    public function activity() { return $this->belongsTo(Activity::class); }
    public function user() { return $this->belongsTo(User::class); }
}
