<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Database\Factories\ProviderVerificationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProviderVerification extends Model
{
    /** @use HasFactory<ProviderVerificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_type',
        'business_address',
        'phone',
        'verification_document',
        'additional_information',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * The travel partner who submitted the verification request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who reviewed (approved/rejected) the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Serve the verification document from Laravel Storage (public disk).
     */
    protected function verificationDocumentUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->verification_document
                ? asset('storage/'.ltrim($this->verification_document, '/'))
                : null,
        );
    }

    /**
     * Whether the request is currently pending review.
     */
    public function isPending(): bool
    {
        return $this->status === VerificationStatus::PENDING->value;
    }

    /**
     * Whether the request has been approved.
     */
    public function isApproved(): bool
    {
        return $this->status === VerificationStatus::APPROVED->value;
    }

    /**
     * Whether the request has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === VerificationStatus::REJECTED->value;
    }

    /**
     * Delete the stored document when the model is removed.
     */
    protected static function booted(): void
    {
        static::deleting(function (ProviderVerification $verification): void {
            if ($verification->verification_document) {
                Storage::disk('public')->delete($verification->verification_document);
            }
        });
    }
}