<?php

namespace Dkbs\LaravelDKBSign\Support;

readonly class Signer
{
    /**
     * @param  list<EnvelopePosition>  $positions
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public int $priority,
        public array $positions = [],
    ) {}

    /**
     * Signer object for envelope `payload.signers[]`.
     *
     * @return array<string, mixed>
     */
    public function toPayloadArray(): array
    {
        /** @var array<string, list<array<string, float|int|string>>> $positionsByDocument */
        $positionsByDocument = [];

        foreach ($this->positions as $position) {
            $index = (string) $position->documentIndex;

            if (! isset($positionsByDocument[$index])) {
                $positionsByDocument[$index] = [];
            }

            $positionsByDocument[$index][] = $position->toMarkArray();
        }

        return [
            'first_name' => $this->firstName,
            'name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'priority' => $this->priority,
            'positions' => $positionsByDocument,
        ];
    }
}
