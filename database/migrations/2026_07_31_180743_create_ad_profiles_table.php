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
        Schema::create('ad_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name', 150);
            $table->string('company_phone', 20);
            $table->string('company_email', 150);
            $table->string('company_website', 150)->nullable();
            $table->string('status')->default('active'); // active, suspended
            $table->softDeletes();
            $table->timestamps();
            $table->comment('Created the first time a user creates an ad campaign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_profiles');
    }
};
