<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Event;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EventNormalizer implements DenormalizerInterface
{
    private const EVENT_TYPE_MAP = [
        'CommitCommentEvent' => 'COM',
        'IssueCommentEvent' => 'MSG',
        'PullRequestEvent' => 'PR',
        'PullRequestReviewCommentEvent' => 'MSG',
        'PushEvent' => 'COM',
    ];

    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return $type === Event::class;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (isset($data['type']) && array_key_exists($data['type'], self::EVENT_TYPE_MAP)) {
            $data['type'] = self::EVENT_TYPE_MAP[$data['type']];
        } else {
            throw new UnsupportedException('Invalid event type');
        }

        return $this->normalizer->denormalize($data, $class, $format, $context);
    }
}
