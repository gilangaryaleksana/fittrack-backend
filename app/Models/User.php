<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'avatar', 'bio',
        'weight', 'height', 'city', 'country',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function activities() { return $this->hasMany(Activity::class); }
    public function gears() { return $this->hasMany(Gear::class); }
    public function kudos() { return $this->hasMany(Kudo::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function photos() { return $this->hasMany(Photo::class); }
    public function segmentEfforts() { return $this->hasMany(SegmentEffort::class); }
    public function createdClubs() { return $this->hasMany(Club::class, 'created_by'); }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withPivot('status', 'created_at');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withPivot('status', 'created_at');
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_members')
            ->withPivot('role', 'joined_at');
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('earned_at');
    }
}
