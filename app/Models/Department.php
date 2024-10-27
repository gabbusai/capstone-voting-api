<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // A department has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // A department has many candidates
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }


    //can have many elections
    public function elections()
    {
        return $this->hasMany(Election::class);
    }
}
