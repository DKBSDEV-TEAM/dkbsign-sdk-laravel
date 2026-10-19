<?php

namespace Dkbs\LaravelDkbsign\Enums;

enum FieldType: string
{
    case SIGNATURE = 'signature';
    case INITIALS = 'initials';
    case SEAL = 'seal';
    case QRCODE = 'qrcode';
    case DATE = 'date';
    case TEXT = 'text';
    case APPROVAL = 'approval';
}
