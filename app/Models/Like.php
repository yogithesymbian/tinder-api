<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['people_id', 'user_id', 'is_like'];

    public function people()
    {
        return $this->belongsTo(People::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
