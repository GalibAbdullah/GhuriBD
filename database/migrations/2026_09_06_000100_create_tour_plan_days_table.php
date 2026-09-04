<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_plan_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number');
            $table->string('title', 150);
            $table->string('theme', 60);
            $table->decimal('budget_allocated', 10, 2);
            $table->text('description');

            // A real, bookable slot the engine found for this day — nulled out
            // (never cascade-deleted) if the guide later removes that slot,
            // since this is only ever a suggestion, not a reservation.
            $table->foreignId('suggested_availability_id')
                ->nullable()
                ->constrained('guide_availabilities')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['tour_plan_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_plan_days');
    }
};
