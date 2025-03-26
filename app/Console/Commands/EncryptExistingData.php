<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptExistingData extends Command
{
    protected $signature = 'encrypt:data';
    protected $description = 'Encrypt existing plaintext data in the database';

    public function handle()
    {
        $this->info('Encrypting existing data...');

        DB::transaction(function () {
            // Encrypt Users
            $this->info('Encrypting User data...');
            DB::table('users')->orderBy('id')->chunk(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'name' => Crypt::encryptString($user->name),
                            'email' => Crypt::encryptString($user->email),
                            // student_id remains unencrypted
                        ]);
                }
            });

            // Encrypt Students
            $this->info('Encrypting Student data...');
            DB::table('students')->orderBy('id')->chunk(100, function ($students) {
                foreach ($students as $student) {
                    DB::table('students')
                        ->where('id', $student->id)
                        ->update([
                            'name' => Crypt::encryptString($student->name),
                        ]);
                }
            });

            // Encrypt TokenOTPs
            $this->info('Encrypting TokenOTP data...');
            DB::table('token_o_t_p_s')->orderBy('id')->chunk(100, function ($tokens) {
                foreach ($tokens as $token) {
                    DB::table('token_o_t_p_s')
                        ->where('id', $token->id)
                        ->update([
                            'tokenOTP' => Crypt::encryptString($token->tokenOTP),
                        ]);
                }
            });

            // Encrypt Votes
            $this->info('Encrypting Vote data...');
            DB::table('votes')->orderBy('id')->chunk(100, function ($votes) {
                foreach ($votes as $vote) {
                    DB::table('votes')
                        ->where('id', $vote->id)
                        ->update([
                            'candidate_name' => Crypt::encryptString($vote->candidate_name),
                            // voter_student_id and candidate_student_id remain unencrypted
                        ]);
                }
            });

            $this->info('Data encryption completed.');
        });

        $this->info('All data encrypted successfully.');
    }
}