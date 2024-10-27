<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    protected $table = 'elections';
    protected $fillable = ['name', 'description'];

    public function electionType()
    {
        return $this->belongsTo(ElectionType::class); // Each Election belongs to one ElectionType
    }

    public function votes()
    {
        return $this->hasMany(Vote::class); // One Election can have many Votes
    }
    
    public function voteStatuses()
    {
        return $this->hasMany(VoteStatus::class); // One Election can have many VoteStatus entries
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class); // One Election can have many Candidates
    }
}
