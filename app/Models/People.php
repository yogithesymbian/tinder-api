<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'latitude',
        'longitude',
        'location',
        'likes_count',
        'notified',
    ];

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
