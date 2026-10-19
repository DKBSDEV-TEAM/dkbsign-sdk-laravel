<?php

namespace Dkbs\LaravelDkbsign\Enums;

enum SignatureLevel: string
{
    case SIMPLE = 'simple';
    case ADVANCED = 'advanced';
    case QUALIFIED = 'qualified';
}
