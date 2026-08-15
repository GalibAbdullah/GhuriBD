<?php

use App\Enums\VerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider_name', 255);
            $table->string('provider_type', 50);
            $table->text('business_address');
            $table->string('phone', 30);
            $table->string('verification_document', 500);
            $table->text('additional_information')->nullable();
            $table->string('status', 20)->default(VerificationStatus::PENDING->value);
            $table->string('rejection_reason', 2000)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_verifications');
    }
};