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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique(); // unique
            $table->string('phone', 20);
            $table->string('password');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('unit_number', 50);
            $table->decimal('rent_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2);
            $table->date('lease_start');
            $table->date('lease_end');
            $table->date('move_in_date');
            $table->date('move_out_date');
            $table->enum('status', ['active', 'expired', 'terminated', 'pending'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['user_id', 'property_id']);

            // Indexes
            $table->index(['user_id']);
            $table->index(['property_id']);
            $table->index(['status']);
            $table->index(['lease_start', 'lease_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
