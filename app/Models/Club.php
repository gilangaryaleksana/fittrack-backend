<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'name', 'description', 'sport_type', 'city', 'cover_photo', 'created_by',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function members()
    {
        return $this->belongsToMany(User::class, 'club_members')
            ->withPivot('role', 'joined_at');
    }
}
