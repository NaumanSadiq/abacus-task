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
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('simulated'); // or 'stripe'
            $table->string('provider_ref')->nullable();       // e.g., Stripe payment_intent id
            $table->string('status')->default('pending');     // pending, succeeded, failed
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 10)->default('usd');
            $table->json('payload')->nullable();              // raw gateway payload
            $table->timestamps();
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
