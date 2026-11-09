<?php

namespace App\Logging;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

/**
 * Formatter qui délègue au formatter d origine puis masque les clés d API (voir RedactSecrets).
 */
class RedactingFormatter implements FormatterInterface
{
    public function __construct(private FormatterInterface $inner)
    {
    }

    public function format(LogRecord $record)
    {
        $formatted = $this->inner->format($record);

        return is_string($formatted) ? RedactSecrets::redact($formatted) : $formatted;
    }

    public function formatBatch(array $records)
    {
        $formatted = $this->inner->formatBatch($records);

        return is_string($formatted) ? RedactSecrets::redact($formatted) : $formatted;
    }
}
