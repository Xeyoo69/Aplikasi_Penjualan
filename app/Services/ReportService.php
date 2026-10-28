<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /**
     * @return array{0: string, 1: string}
     */
    public function range(?string $from, ?string $to): array
    {
        try {
            $start = Carbon::parse($from ?: now()->startOfMonth()->toDateString())->startOfDay();
            $end = Carbon::parse($to ?: now()->toDateString())->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'date_from' => 'Format tanggal tidak valid.',
            ]);
        }

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'date_to' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            ]);
        }

        return [$start->toDateString(), $end->toDateString()];
    }

    public function summary(string $from, string $to): array
    {
        $row = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(subtotal),0) as subtotal, COALESCE(SUM(discount_amount),0) as discount, COALESCE(SUM(tax_amount),0) as tax, COALESCE(SUM(total),0) as total')
            ->first();

        return [
            'transactions' => (int) ($row->transactions ?? 0),
            'subtotal' => (int) ($row->subtotal ?? 0),
            'discount' => (int) ($row->discount ?? 0),
            'tax' => (int) ($row->tax ?? 0),
            'total' => (int) ($row->total ?? 0),
        ];
    }

    public function salesByDay(string $from, string $to): Builder
    {
        return DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as transactions, COALESCE(SUM(subtotal),0) as subtotal, COALESCE(SUM(discount_amount),0) as discount, COALESCE(SUM(tax_amount),0) as tax, COALESCE(SUM(total),0) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('sale_date');
    }

    public function topProducts(string $from, string $to): Builder
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('sale_items.product_name, sale_items.sku, COALESCE(SUM(sale_items.qty),0) as qty, COALESCE(SUM(sale_items.subtotal),0) as total')
            ->groupBy('sale_items.product_name', 'sale_items.sku')
            ->orderByDesc('qty');
    }

    public function perCashier(string $from, string $to): Builder
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('users.id, users.name, COUNT(sales.id) as transactions, COALESCE(SUM(sales.total),0) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total');
    }

    public function bestSellers(string $from, string $to, int $limit = 5)
    {
        return $this->topProducts($from, $to)->limit($limit)->get();
    }
}
