<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenOTP extends Model
{
    // Explicitly specify the table name if it's different from the default plural form of the model name
    protected $table = 'token_o_t_p_s';  // Make sure this matches your table name

    // Disable the automatic handling of timestamps if you're not using created_at and updated_at fields
    public $timestamps = true;  // Set to false if you're not using timestamps

    // Specify the columns that can be mass-assigned
    protected $fillable = [
        'user_id',
        'expires_at',
        'used',
        'student_id',
        'email' ,
        'tokenOTP' ,
        'device_id',
    ];

     //You can also define relationships if needed, for example:
     public function user() {
        return $this->belongsTo(User::class);
     }
}
