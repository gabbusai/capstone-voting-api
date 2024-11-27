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
        Schema::create('elections', function (Blueprint $table) {
        $table->id(); // Election ID
        $table->foreignId('election_type_id')->constrained('election_types')->onDelete('cascade'); // Foreign key to ElectionTypes table
        $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade'); // Foreign key to Departments table, nullable for elections open to all
        $table->string('election_name'); // Election name

        $table->dateTime('campaign_start_date');//start of campaigning
        $table->dateTime('campaign_end_date');//end of campaigning
        
        $table->dateTime('election_start_date'); // Start date and time
        $table->dateTime('election_end_date'); // End date and time
        $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming'); // Status of the election
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
