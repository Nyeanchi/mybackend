<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id()->comment('Unique ID for the setting record');
            $table->string('platform_name')->nullable()->comment('Name of the platform');
            $table->string('default_language')->nullable()->comment('Default system language');
            $table->string('default_currency')->nullable()->comment('Default system currency');
            $table->string('default_timezone')->nullable()->comment('Default system timezone');
            $table->text('description')->nullable()->comment('General description of the platform');
            $table->json('currency_rates')->nullable()->comment('Exchange rates for supported currencies');
            $table->json('fees')->nullable()->comment('Platform-wide fees (transaction %, service fees, etc.)');
            $table->json('features')->nullable()->comment('Feature toggles (enable/disable modules)');
            $table->unsignedInteger('auth_max_login_attempts')->default(5)->comment('Max login attempts before lockout');
            $table->unsignedInteger('auth_session_timeout')->default(24)->comment('Session timeout in hours');
            $table->json('password_policy')->nullable()->comment('Rules (min length, complexity, expiry)');
            $table->json('notif_channels')->nullable()->comment('Channels enabled (email, SMS, push)');
            $table->json('notif_types')->nullable()->comment('Types of notifications (maintenance, payments, updates, messages)');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict')->comment('Admin user who configured it');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};
