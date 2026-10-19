<?php

namespace Dkbs\LaravelDKBSign\Enums;

enum SignatureLevel: string
{
    case SIMPLE = 'simple';
    case ADVANCED = 'advanced';
    case QUALIFIED = 'qualified';
}
