<?php

use App\Enums\RoomStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resort_id')->constrained()->cascadeOnDelete();
            $table->string('room_name', 255);
            $table->string('room_type', 50);
            $table->text('description');
            $table->decimal('price_per_night', 10, 2);
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('total_rooms');
            $table->unsignedInteger('available_rooms');
            $table->string('bed_type', 100);
            $table->string('room_size', 100);
            $table->json('amenities')->nullable();
            $table->string('cover_image', 500);
            $table->string('status', 20)->default(RoomStatus::AVAILABLE->value);
            $table->timestamps();

            $table->index('resort_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
