<?php

namespace App\Repository;

use App\Dto\EventInput;
use RuntimeException;

interface WriteEventRepository
{
    /**
     * @param EventInput $authorInput
     * @param int $id
     * @return void
     * @throws RuntimeException
     */
    public function update(EventInput $authorInput, int $id): void;
}
