<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('vote_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Voter
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->onDelete('cascade'); // Candidate
            $table->foreignId('election_id')->constrained('elections')->onDelete('cascade'); // Election
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('cascade'); // Position
            $table->enum('action', ['VOTE_CAST', 'DUPLICATE_ATTEMPT'])->default('VOTE_CAST'); // Type of log
            $table->text('message')->nullable(); // Details of the log
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_logs');
    }
};
