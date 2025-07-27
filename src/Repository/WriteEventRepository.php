<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\EventInput;

interface WriteEventRepository
{
    public function update(EventInput $authorInput, int $id): void;
}
