<?php

declare(strict_types=1);

namespace Src\Room\Infrastructure\Policies;

use Src\Shared\Infrastructure\Policies\ModulePolicy;

final class RoomPolicy extends ModulePolicy
{
    public function __construct()
    {
        parent::__construct('room');
    }
}
