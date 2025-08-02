<?php

namespace App\Hydrator;

use App\DBAL\Types\EventType;
use App\Entity\Event;

class EventHydrator
{
    public function setEventFromArray(array $array): Event
    {
        $event = new Event();
        EventType::assertValidChoice($array['type']);
        $event->setId($array['id'])
            ->setType($array['type'])
            ->setPayload($array['payload'] ?? [])
            ->setCreatedAt(new \DateTimeImmutable($array['created_at']))
            ->setComment($array['payload']['comment']['body'] ?? null);
        if (EventType::COMMIT === $array['type']) {
            $event->setCount($payload['size'] ?? 1);
        }

        return $event;

    }
}
