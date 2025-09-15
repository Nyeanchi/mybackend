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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('FCFA');
            $table->enum('payment_type', ['rent', 'deposit', 'maintenance', 'other'])->default('rent');
            $table->enum('payment_period', ['monthly', 'quarterly', 'yearly', 'custom'])->default('monthly');

            // Dates
            $table->date('due_date')->nullable();;
            $table->dateTime('paid_date')->nullable();

            // Status and references
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_reference')->nullable();
            $table->string('receipt_number')->nullable()->unique();

            // Additional fields
            $table->decimal('late_fees', 10, 2)->default(0);
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes for better performance
            $table->index('tenant_id');
            $table->index('property_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('paid_date');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
