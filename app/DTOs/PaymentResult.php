<?php

namespace App\DTOs;

/**
 * Value object representing the result of a payment gateway operation.
 */
readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public string $message,
    ) {}

    /**
     * Create a successful payment result.
     */
    public static function successful(string $transactionId, string $message = 'Payment processed successfully.'): self
    {
        return new self(
            success: true,
            transactionId: $transactionId,
            message: $message,
        );
    }

    /**
     * Create a failed payment result.
     */
    public static function failed(string $message = 'Payment processing failed.'): self
    {
        return new self(
            success: false,
            transactionId: '',
            message: $message,
        );
    }
}
