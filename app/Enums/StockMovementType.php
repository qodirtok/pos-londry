<?php
namespace App\Enums;
enum StockMovementType: string { case STOCK_IN='stock_in'; case STOCK_OUT='stock_out'; case ADJUSTMENT='adjustment'; case SALE='sale'; case RETURN='return'; }
