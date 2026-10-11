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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();

            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->dateTime('blocked_until');
            $table->dateTime('active_slot_at')->nullable();

            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('price_amount');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('buffer_minutes');

            $table->text('customer_note')->nullable();
            $table->text('staff_note')->nullable();

            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['staff_profile_id', 'starts_at', 'blocked_until']);
            $table->index(['customer_id', 'starts_at']);
            $table->unique(['staff_profile_id', 'active_slot_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
