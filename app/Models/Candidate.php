<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'user_id', 'department_id', 
                        'position_id', 'party_list_id' , 'election_id'];

    protected static function boot(){
    parent::boot();
    static::deleting(function ($candidate) {
    // Delete the profile photo
    if ($candidate->profile_photo) {
        Storage::disk('public')->delete($candidate->profile_photo);
    }
    // Delete all posts related to the candidate and their images
    foreach ($candidate->posts as $post) {
        if ($post->image) 
        {
        Storage::disk('public')->delete($post->image);
        }
        $post->delete();
    }
    });
    }


    // A candidate belongs to a student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // A candidate belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //A candidacy belongs to an Election
    public function election(){
        return $this->belongsTo(Election::class);
    }

    // A candidate belongs to a department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // A candidate belongs to a position
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // A candidate belongs to a party list
    public function partyList()
    {
        return $this->belongsTo(PartyList::class);
    }

    // A candidate has many posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // A candidate can have many votes
    public function votes()
    {
        return $this->hasMany(Vote::class); // Link to Vote model
    }

    // A candidate can have many results
    public function results()
    {
        return $this->hasMany(Result::class); // Link to Result model
    }

    
}
