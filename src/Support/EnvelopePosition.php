<?php

namespace Dkbs\LaravelDkbsign\Support;

use Dkbs\LaravelDkbsign\Enums\FieldType;

readonly class EnvelopePosition
{
    public function __construct(
        public float $x,
        public float $y,
        public float $width,
        public float $height,
        public int $page,
        public string|FieldType $fieldType,
        public int $documentIndex,
    ) {}

    /**
     * One mark on a document (API payload inside positions["0"], etc.).
     *
     * @return array<string, float|int|string>
     */
    public function toMarkArray(): array
    {
        $type = $this->fieldType instanceof FieldType
            ? $this->fieldType->value
            : $this->fieldType;

        return [
            'page' => $this->page,
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $type,
        ];
    }
}
