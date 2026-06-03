<?php

declare(strict_types=1);

namespace Src\Reminder\Domain\Enums;

enum ReminderStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
