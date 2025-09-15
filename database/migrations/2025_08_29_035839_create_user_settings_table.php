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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('language', ['en', 'fr'])->default('fr');
            $table->enum('theme', ['light', 'dark', 'system'])->default('light');
            $table->enum('currency_preference', ['FCFA', 'USD', 'EUR'])->default('FCFA');
            $table->string('timezone', 50)->default('Africa/Douala');
            $table->string('date_format', 20)->default('DD/MM/YYYY');
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('payment_reminders')->default(true);
            $table->boolean('maintenance_updates')->default(true);
            $table->json('privacy_settings')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
