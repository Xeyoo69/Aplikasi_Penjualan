<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Sale = 'sale';
    case Restock = 'restock';
    case Adjustment = 'adjustment';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Penjualan',
            self::Restock => 'Restock',
            self::Adjustment => 'Penyesuaian',
            self::Void => 'Void',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Sale => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::Restock => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Adjustment => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Void => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        };
    }
}
