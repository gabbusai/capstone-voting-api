<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class); // Link to User model
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class); // Link to Candidate model
    }

    public function election()
    {
        return $this->belongsTo(Election::class); // Link to Election model
    }
}
