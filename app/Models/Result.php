<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'results';

    public function election()
    {
        return $this->belongsTo(Election::class); // Each Result belongs to one Election
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class); // Each Result belongs to one Candidate
    }

    public function position(){
        return $this->belongsTo(Position::class); //belongs to one Position
    }
}
