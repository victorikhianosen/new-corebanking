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
            // Organization
            $table->string('code')->unique();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('short_name')->nullable();

            // Organization Type
            $table->enum('type', [
                'bank',
                'microfinance',
                'fintech',
                'investment',
                'insurance',
                'cooperative',
                'other',
            ]);

            // Registration
            $table->string('registration_number')->nullable();
            $table->string('license_number')->nullable();
            $table->string('tax_identification_number')->nullable();

            // Contact
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();

            // Address
            $table->string('country')->default('Nigeria');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();

            // Branding
            $table->string('logo')->nullable();

            // Localization
            $table->string('timezone')->default('Africa/Lagos');
            $table->string('currency', 10)->default('NGN');
            $table->string('locale', 10)->default('en');

            // Database Connection
            $table->string('database_connection')->default('tenant');
            $table->string('database_host');
            $table->unsignedSmallInteger('database_port')->default(3306);
            $table->string('database_name')->unique();
            $table->string('database_username');
            $table->text('database_password');
            $table->string('api_secret')->unique();

            // Domain
            $table->string('domain')->nullable();
            $table->string('subdomain')->nullable();

            // Status
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('code');
            $table->index('type');
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