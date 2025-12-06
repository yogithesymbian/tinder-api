<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = ['people_id', 'url', 'caption', 'order'];

    public function people()
    {
        return $this->belongsTo(People::class);
    }
}
