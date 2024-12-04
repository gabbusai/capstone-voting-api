<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'name'];

    // A student can have one associated user account
    public function user()
    {
        return $this->hasOne(User::class);
    }

    // A student may also be a candidate
    public function candidate()
    {
        return $this->hasOne(Candidate::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }
}
