<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    
    protected $fillable = ['user_id', 'stars', 'title', 'content'];
    // A student can have one associated user account
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
