<?php

namespace Dkbs\LaravelDKBSign\Enums;

enum SignatureOrder: string
{
    case ORDERED = 'ordered';
    case ANY = 'any';
}
