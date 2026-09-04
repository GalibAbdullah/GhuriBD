<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 30);

            // The gateway's own transaction id. Unique so a replayed or
            // double-submitted callback is recognised, not processed twice.
            $table->string('gateway_reference', 100)->unique();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->string('status', 20)->default(PaymentStatus::PENDING->value);
            $table->string('failure_reason', 500)->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
