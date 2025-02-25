<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoteStatus extends Model
{
    protected $table = 'vote_statuses';
    protected $fillable = ['user_id', 'election_id', 'voted_at', 'has_voted' ];
    public function user()
    {
        return $this->belongsTo(User::class); // Link to User model
    }

    public function election()
    {
        return $this->belongsTo(Election::class); // Link to Election model
    }

    public function votes(){
        return $this->hasMany(Vote::class);
    }
}
