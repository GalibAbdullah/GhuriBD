<?php

namespace App\Payments;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Machine name stored on Payment::gateway, e.g. "mock" or "sslcommerz".
     */
    public function name(): string;

    /**
     * Start a payment attempt for a booking and report where to send the
     * traveler next.
     */
    public function initiate(Booking $booking): PaymentSession;

    /**
     * Interpret a gateway callback against a specific payment attempt. Does
     * not itself mutate the Payment or Booking — the caller decides how to
     * apply the result inside its own transaction.
     */
    public function handleCallback(Payment $payment, Request $request): PaymentResult;

    /**
     * Refund a previously succeeded payment. Returns false if the gateway
     * declines the refund (e.g. already refunded upstream).
     */
    public function refund(Payment $payment): bool;
}
