<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resort_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tour_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_type', 20);
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->date('travel_date')->nullable();
            $table->unsignedInteger('guests');
            $table->decimal('total_amount', 10, 2);
            $table->string('booking_status', 20)->default(BookingStatus::PENDING->value);
            $table->string('payment_status', 20)->default(PaymentStatus::PENDING->value);
            $table->string('booking_reference', 20)->unique();
            $table->text('special_request')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('resort_id');
            $table->index('room_id');
            $table->index('tour_package_id');
            $table->index('booking_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
