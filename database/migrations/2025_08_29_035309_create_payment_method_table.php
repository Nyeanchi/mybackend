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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_fr', 100);
            $table->string('code', 50)->unique();
            $table->string('provider', 100)->nullable();
            $table->decimal('fees_percent', 5, 4)->default(0.0000);
            $table->decimal('fees_fixed', 10, 2)->default(0.00);
            $table->enum('currency', ['FCFA', 'USD', 'EUR'])->default('FCFA');
            $table->boolean('requires_phone')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('icon_url', 500)->nullable();
            $table->timestamps();


            // Indexes
            $table->index(['code']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
