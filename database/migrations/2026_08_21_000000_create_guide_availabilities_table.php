<?php

use App\Enums\AvailabilityStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_availabilities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('available_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->unsignedSmallInteger('booked_count')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status', 20)->default(AvailabilityStatus::AVAILABLE->value);
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            // Two concurrent submits can both pass the application-level overlap
            // check before either commits; this makes the database the final
            // arbiter for the exact-duplicate case.
            $table->unique(['user_id', 'available_date', 'start_time'], 'guide_availabilities_slot_unique');

            $table->index(['user_id', 'available_date']);
            $table->index(['available_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_availabilities');
    }
};
