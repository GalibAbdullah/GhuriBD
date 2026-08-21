<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'gateway',
        'gateway_reference',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'gateway_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'gateway_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isInitiated(): bool
    {
        return $this->status === PaymentStatus::INITIATED;
    }

    public function isSucceeded(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED;
    }
}
