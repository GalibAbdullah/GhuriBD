<?php

use App\Enums\TourPackageStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('destination', 255);
            $table->string('division', 50);
            $table->string('district', 50);
            $table->text('description');
            $table->unsignedInteger('duration_days');
            $table->unsignedInteger('duration_nights');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('max_travelers');
            $table->string('meeting_point', 255);
            $table->string('start_location', 255);
            $table->longText('itinerary');
            $table->json('included_services')->nullable();
            $table->json('excluded_services')->nullable();
            $table->string('cover_image', 500);
            $table->string('status', 20)->default(TourPackageStatus::ACTIVE->value);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
