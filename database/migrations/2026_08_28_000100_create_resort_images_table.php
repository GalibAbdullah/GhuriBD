<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resort_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resort_id')->constrained()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->timestamps();

            $table->index('resort_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resort_images');
    }
};
