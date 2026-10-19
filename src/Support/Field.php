<?php

namespace Dkbs\LaravelDKBSign\Support;

use Dkbs\LaravelDKBSign\Enums\FieldType;
use InvalidArgumentException;

class Field
{
    private readonly string $type;

    private ?Position $position = null;

    public function __construct(string|FieldType $type)
    {
        $this->type = $type instanceof FieldType ? $type->value : $type;
    }

    public function usePosition(Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        if ($this->position === null) {
            throw new InvalidArgumentException('Field position is required. Call usePosition() first.');
        }

        return [
            'type' => $this->type,
            ...$this->position->toArray(),
        ];
    }
}
