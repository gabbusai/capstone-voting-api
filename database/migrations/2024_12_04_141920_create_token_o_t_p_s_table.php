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
        Schema::create('token_o_t_p_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key for user_id
            $table->string('tokenOTP'); // The OTP token
            $table->dateTime('expires_at')->nullable(); // The expiration time for the OTP
            $table->boolean('used')->default(false); // Tracks if the OTP has been used
            $table->timestamps();
        });
    }
    
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_o_t_p_s');
    }
};
