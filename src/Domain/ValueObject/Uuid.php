<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use InvalidArgumentException;

/**
 * Value Object pre UUID
 */
class Uuid
{
    private string $value;

    /**
     * Konštruktor
     *
     * @param string $value UUID hodnota
     * @throws InvalidArgumentException Ak je UUID neplatné
     */
    public function __construct(string $value)
    {
        if (!RamseyUuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID: {$value}");
        }

        $this->value = $value;
    }

    /**
     * Vygeneruje nové UUID
     *
     * @return self
     */
    public static function generate(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    /**
     * Vytvorí nil UUID (00000000-0000-0000-0000-000000000000)
     *
     * @return self
     */
    public static function nil(): self
    {
        return new self(RamseyUuid::NIL);
    }

    /**
     * Vytvorí UUID z reťazca
     *
     * @param string $value UUID hodnota
     * @return self|null UUID objekt alebo null, ak je hodnota neplatná
     */
    public static function fromString(string $value): ?self
    {
        try {
            return new self($value);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Vráti hodnotu UUID
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Porovná s iným UUID
     *
     * @param self $other Iné UUID
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->getValue();
    }

    /**
     * Konverzia na reťazec
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
