<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            // The referral or agent code typed or linked at sign-up. POST customers runs later (after the
            // e-mail is verified, possibly on another device), so the code must live on the row.
            $table->string('signup_referral_code', 32)->nullable()->after('linked_id');
            $table->timestamp('referral_verified_reported_at')->nullable()->after('signup_referral_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_verified_at', 'signup_referral_code', 'referral_verified_reported_at']);
        });
    }
};
