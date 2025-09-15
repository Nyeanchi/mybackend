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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique(); // unique
            $table->string('phone', 20);
            $table->string('password');

            // Role & status
            $table->enum('role', ['landlord', 'tenant', 'admin'])->default('tenant');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');

            // Profile
            $table->string('avatar', 500)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();

            // Foreign keys
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');

            // Emergency contacts
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Verification
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // Laravel timestamps
            $table->timestamps();

            // Indexes
            $table->index(['email']);
            $table->index(['role']);
            $table->index(['status']);
            $table->index(['phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
