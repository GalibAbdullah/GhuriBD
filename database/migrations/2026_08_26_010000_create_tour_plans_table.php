<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('traveler_id')->constrained('users')->cascadeOnDelete();
            $table->string('destination', 120);
            $table->date('start_date')->nullable();
            // Not "days" — that collides with the days() relation to
            // TourPlanDay, and an Eloquent attribute always wins over a
            // same-named relation on property access ($plan->days would
            // silently return this integer instead of the itinerary rows).
            $table->unsignedTinyInteger('duration_days');
            $table->decimal('budget', 10, 2);

            // A validated subset of App\Enums\Interest values.
            $table->json('interests');

            $table->timestamp('regenerated_at')->nullable();
            $table->timestamps();

            $table->index('traveler_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_plans');
    }
};
