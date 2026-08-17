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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->index()->constrained('orders')->cascadeOnDelete();
            $table->string('recipient_name', 120);
            $table->string('phone', 20);
            $table->string('email', 160);
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('city', 120);
            $table->string('county', 120)->nullable();
            $table->string('country', 2)->default('KE');
            $table->string('postal_code', 20)->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
