<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Exceptions;

use RuntimeException;

final class InsufficientCreditsException extends RuntimeException
{
    public function __construct(
        public readonly int $requiredCredits,
        public readonly int $currentBalance,
        string $message = ''
    ) {
        $msg = $message !== ''
            ? $message
            : "Saldo kredit AI Studio tidak mencukupi (dibutuhkan: {$requiredCredits}, tersedia: {$currentBalance}).";

        parent::__construct($msg);
    }
}
