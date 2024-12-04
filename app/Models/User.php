<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens ,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 
        'student_id', 'department_id', 'role_id', 
        'device_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id'); // Link to Student model
    }

    // A user belongs to one department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // A user belongs to one role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // A user can be a candidate
    public function candidate()
    {
        return $this->hasOne(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class); // Link to Vote model
    }

    public function voteStatuses()
    {
        return $this->hasMany(VoteStatus::class); // Link to VoteStatus model
    }

    public function verification_codes()
    {
        return $this->hasMany(VerificationCodes::class);
    }

    public function tokenOTPs(){
        return $this->hasMany(TokenOTP::class);
    }
}
