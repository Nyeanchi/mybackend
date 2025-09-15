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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address');
            $table->foreignId('city_id')->constrained()->onDelete('restrict');
            $table->foreignId('region_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['villa', 'apartment', 'studio', 'house', 'commercial', 'office', 'others'])->default('house');
            $table->unsignedInteger('total_units')->default(1);
            $table->unsignedInteger('available_units')->default(1);
            $table->decimal('rent_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2);
            $table->enum('currency', ['FCFA', 'USD', 'EUR'])->default('FCFA');
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();

            // Indexes
            $table->index(['landlord_id']);
            $table->index(['city_id', 'region_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['rent_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
