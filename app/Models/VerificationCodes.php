<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCodes extends Model
{
    protected $fillable = [
    'user_id', 'code', 'expiration_date', 'isExpired'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
