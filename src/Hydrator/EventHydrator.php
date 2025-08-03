<?php

namespace App\Hydrator;

use App\Enums\EventType;
use App\Entity\Event;

class EventHydrator
{
    public function setEventFromArray(array $array): Event
    {
        $event = new Event();
        $event->setId($array['id'])
            ->setType(EventType::from($array['type']))
            ->setPayload($array['payload'] ?? [])
            ->setCreatedAt(new \DateTimeImmutable($array['created_at']))
            ->setComment($array['payload']['comment']['body'] ?? null);
        if (EventType::COMMIT === EventType::from($array['type'])) {
            $event->setCount($payload['size'] ?? 1);
        }

        return $event;

    }
}
