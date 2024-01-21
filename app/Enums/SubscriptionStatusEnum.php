<?php

namespace App\Enums;

enum SubscriptionStatusEnum: string
{
    case CANCEL = 'cancel';
    case REFUND = 'refund';
}
