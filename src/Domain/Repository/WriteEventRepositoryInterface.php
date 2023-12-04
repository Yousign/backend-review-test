<?php


namespace App\Domain\Repository;

use App\Application\Dto\EventInput;

interface WriteEventRepositoryInterface
{
    public function update(EventInput $authorInput, int $id): void;
}
