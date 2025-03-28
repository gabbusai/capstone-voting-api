<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    protected $table = 'elections';
    protected $fillable = [
        'election_name',
        'election_type_id',
        'campaign_start_date',
        'campaign_end_date',
        'election_start_date',
        'election_end_date',
        'status',
        'department_id', // If applicable
        'created_at',    // Optional
        'updated_at'     // Optional
    ];

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

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function departments()
    {
        return $this->belongsTo(Department::class);
    }

}
