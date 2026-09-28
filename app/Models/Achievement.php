<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'icon', 'criteria'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('earned_at');
    }
}
