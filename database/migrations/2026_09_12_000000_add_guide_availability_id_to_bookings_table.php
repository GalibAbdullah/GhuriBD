<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('guide_availability_id')->nullable()->after('tour_package_id')->constrained('guide_availabilities')->nullOnDelete();

            $table->index('guide_availability_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('guide_availability_id');
        });
    }
};
