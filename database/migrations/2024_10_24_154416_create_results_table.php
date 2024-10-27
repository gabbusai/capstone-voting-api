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
        Schema::create('results', function (Blueprint $table) {
            $table->id(); // Result ID
            $table->foreignId('election_id')->constrained('elections')->onDelete('cascade');// Foreign key to Elections table
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade'); 
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade'); // Foreign key to Candidates table
            $table->integer('vote_count')->default(0); // Total votes received by the candidate
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
