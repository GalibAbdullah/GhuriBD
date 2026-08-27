<?php

use App\Enums\ResortStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resorts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description');
            $table->string('division', 50);
            $table->string('district', 50);
            $table->text('address');
            $table->string('contact_phone', 30);
            $table->string('price_range', 100);
            $table->json('amenities')->nullable();
            $table->string('cover_image', 500);
            $table->string('status', 20)->default(ResortStatus::ACTIVE->value);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resorts');
    }
};
