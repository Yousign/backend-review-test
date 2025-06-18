<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * Enum for GitHub event types used in the Event entity.
 *
 * @extends AbstractEnumType<string, string>
 * @method static string getReadableValue(string $value)
 */
final class EventType extends AbstractEnumType
{
    public const string COMMIT = 'COM';
    public const string COMMENT = 'MSG';
    public const string PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];

    /**
     * Optional helper to get enum as an associative array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return self::$choices;
    }
}
