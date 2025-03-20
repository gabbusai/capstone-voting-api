<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyList extends Model
{
    use HasFactory;

    protected $table = 'party_lists';
    protected $fillable = ['name', 'description'];

    // A party list has many candidates
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
