<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('traveler_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic on purpose: Resort and Tour Package booking (other
            // members' Sprint 3 work) reuse this same booking/payment core
            // instead of duplicating it per product type.
            $table->string('bookable_type');
            $table->unsignedBigInteger('bookable_id');

            $table->unsignedTinyInteger('party_size')->default(1);

            // Snapshots at booking time — a later price change on the
            // underlying slot must never reprice a traveler's existing booking.
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            $table->string('status', 20)->default(BookingStatus::PENDING_PAYMENT->value);
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['bookable_type', 'bookable_id']);
            $table->index(['traveler_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
