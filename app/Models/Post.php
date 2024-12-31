<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['candidate_id', 'title', 'content', 'image', 'is_approved'];

    // A post belongs to a candidate
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
