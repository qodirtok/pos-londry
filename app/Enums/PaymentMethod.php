<?php
namespace App\Enums;
enum PaymentMethod: string {
    case CASH='cash'; case TRANSFER='transfer'; case QRIS='qris';
    case DEBIT='debit'; case CREDIT_CARD='credit_card'; case E_WALLET='e_wallet';
}
