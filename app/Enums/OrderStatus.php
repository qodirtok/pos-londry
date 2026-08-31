<?php
namespace App\Enums;
enum OrderStatus: string {
    case RECEIVED='received';
    case WASHING='washing';
    case DRYING='drying';
    case IRONING='ironing';
    case READY='ready';
    case PICKED_UP='picked_up';
    case CANCELLED='cancelled';
    public static function flow(): array {
        return [self::RECEIVED,self::WASHING,self::DRYING,self::IRONING,self::READY,self::PICKED_UP];
    }
}
