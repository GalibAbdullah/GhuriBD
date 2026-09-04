<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Stands in for a real gateway (e.g. SSLCommerz) with an in-app "checkout"
 * page the traveler approves or declines. No external credentials, no
 * network call — but the same initiate/callback/refund contract a real
 * gateway would use, so swapping one in later is a new class, not a rewrite.
 */
class MockPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'mock';
    }

    public function initiate(Booking $booking): PaymentSession
    {
        $payment = $booking->payments()->create([
            'gateway' => $this->name(),
            'gateway_reference' => 'MOCK-'.strtoupper(Str::random(12)),
            'amount' => $booking->total_amount,
            'currency' => config('ghuribd.currency.code', 'BDT'),
            'status' => PaymentStatus::PENDING->value,
        ]);

        return new PaymentSession(
            payment: $payment,
            redirectUrl: route('payments.show', $payment),
        );
    }

    public function handleCallback(Payment $payment, Request $request): PaymentResult
    {
        $decision = $request->string('decision')->toString();

        if ($decision === 'approve') {
            return PaymentResult::success($payment->gateway_reference, ['decision' => 'approve']);
        }

        return PaymentResult::failure(
            $payment->gateway_reference,
            'The payment was declined in the mock checkout.',
            ['decision' => $decision],
        );
    }

    public function refund(Payment $payment): bool
    {
        return true;
    }
}
