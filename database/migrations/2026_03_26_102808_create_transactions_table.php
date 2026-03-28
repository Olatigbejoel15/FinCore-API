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
         Schema::create('transactions', function (Blueprint $table) {
        $table->id(); // Primary key

        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        // Connects transaction to a user

        $table->string('type');
        // Type of transaction: deposit or withdrawal

        $table->decimal('amount', 12, 2);
        // Stores transaction amount (e.g. 5000.00)

        $table->string('description')->nullable();
        // Optional description like "Initial deposit"

        $table->timestamps();
        // created_at and updated_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
