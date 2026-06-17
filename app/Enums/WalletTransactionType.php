<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case RefundCredit = 'refund_credit';
    case AdminAdjust = 'admin_adjust';
    case PaymentDebit = 'payment_debit';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
