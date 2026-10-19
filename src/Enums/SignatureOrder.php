<?php

namespace Dkbs\LaravelDkbsign\Enums;

enum SignatureOrder: string
{
    case ORDERED = 'ordered';
    case ANY = 'any';
}
