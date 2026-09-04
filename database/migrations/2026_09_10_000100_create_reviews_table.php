<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // One review per booking — also determines which of resort_id /
            // tour_package_id below applies, copied from the booking at
            // creation time for cheap querying on the resort/package pages.
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('resort_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('tour_package_id')->nullable()->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('review_text');
            $table->text('partner_reply')->nullable();
            $table->timestamps();

            $table->index('resort_id');
            $table->index('tour_package_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
