<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
        $table->id(); // Vote ID
        //voteExists =  user -> has votes -> where ('position_id') exists and where election_id = 'current election'
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key to Students table
        $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
        $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade'); // Foreign key to Candidates table
        $table->foreignId('election_id')->constrained('elections')->onDelete('cascade'); // Foreign key to Elections table
        $table->foreignId('vote_status_id')->constrained('vote_statuses')->onDelete('cascade');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
