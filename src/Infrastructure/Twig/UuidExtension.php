<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Domain\ValueObject\Uuid;

/**
 * Twig extension pre prácu s UUID
 */
class UuidExtension extends AbstractExtension
{
    /**
     * Vráti zoznam funkcií pre Twig
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_uuid', [$this, 'generateUuid']),
            new TwigFunction('is_valid_uuid', [$this, 'isValidUuid']),
            new TwigFunction('uuid_short', [$this, 'uuidShort']),
        ];
    }

    /**
     * Vygeneruje nové UUID
     *
     * @return string
     */
    public function generateUuid(): string
    {
        return (string) Uuid::generate();
    }

    /**
     * Overí, či je reťazec platným UUID
     *
     * @param string $uuid
     * @return bool
     */
    public function isValidUuid(string $uuid): bool
    {
        return Uuid::fromString($uuid) !== null;
    }

    /**
     * Vráti skrátenú verziu UUID
     *
     * @param string $uuid
     * @param int $length
     * @return string
     */
    public function uuidShort(string $uuid, int $length = 8): string
    {
        if (Uuid::fromString($uuid) === null) {
            return $uuid;
        }

        return substr($uuid, 0, $length) . '...';
    }
}
