<?php

namespace App\Enums;

enum CheckoutSessionModeEnum: string
{
    case SUB = 'subscription';
    case PAY = 'payment';
}
