<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case OPEN = 'Open';
    case IN_PROGRESS = 'In Progress';
    case RESOLVED = 'Resolved';
    case CLOSED = 'Closed';

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function isSettled(): bool
    {
        return in_array($this, [self::RESOLVED, self::CLOSED], true);
    }
}
