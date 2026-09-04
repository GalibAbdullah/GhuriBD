<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resort_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('tour_package_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            // A traveler can save the same resort or package only once.
            $table->unique(['user_id', 'resort_id']);
            $table->unique(['user_id', 'tour_package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
