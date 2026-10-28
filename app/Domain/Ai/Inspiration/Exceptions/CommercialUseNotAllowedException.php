<?php

declare(strict_types=1);

namespace App\Domain\Ai\Inspiration\Exceptions;

use RuntimeException;

final class CommercialUseNotAllowedException extends RuntimeException
{
    public function __construct(
        public readonly string $licenseName,
        string $message = ''
    ) {
        $msg = $message !== ''
            ? $message
            : "Lisensi referensi visual ('{$licenseName}') tidak mengizinkan pemrosesan atau penggunaan untuk materi iklan komersial.";

        parent::__construct($msg);
    }
}
