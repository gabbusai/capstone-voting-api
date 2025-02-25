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
        Schema::create('vote_statuses', function (Blueprint $table) {
            $table->id(); // Vote status ID
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key to Students table
            $table->foreignId('election_id')->constrained('elections')->onDelete('cascade'); // Foreign key to Elections table
            $table->date('voted_at')->nullable(); // Date of when the vote was cast
            $table->boolean('has_voted')->default(false); // Flag indicating if the student has voted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_statuses');
    }
};
