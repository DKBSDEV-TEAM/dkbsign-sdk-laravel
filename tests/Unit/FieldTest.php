<?php

namespace Dkbs\LaravelDKBSign\Tests\Unit;

use Dkbs\LaravelDKBSign\Enums\FieldType;
use Dkbs\LaravelDKBSign\Support\Field;
use Dkbs\LaravelDKBSign\Support\Position;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function test_to_array_includes_type_and_position(): void
    {
        $field = (new Field(FieldType::SIGNATURE))
            ->usePosition(new Position(x: 120, y: 200, width: 200, height: 70));

        $this->assertSame([
            'type' => 'signature',
            'x' => 120.0,
            'y' => 200.0,
            'width' => 200.0,
            'height' => 70.0,
        ], $field->toArray());
    }
}
