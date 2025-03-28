<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['user_id', 'voter_student_id', 'position_id', 'position_name' ,'candidate_student_id', 
    'candidate_id', 'candidate_name', 'election_id'];

    protected $casts = [
        'candidate_name' => 'encrypted',
        // voter_student_id and candidate_student_id stay unencrypted (integers)
    ];

    public function voter()
    {
        return $this->belongsTo(Student::class, 'voter_student_id', 'id');
    }

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

    public function vote_status(){
        return $this->belongsTo(VoteStatus::class); // Link to VoteStatus model
    }
}
