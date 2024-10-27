<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionType extends Model
{
    
    protected $table = 'election_types';

    public function elections()
    {
        return $this->hasMany(Election::class); // One ElectionType can have many Elections
    }
    
}
