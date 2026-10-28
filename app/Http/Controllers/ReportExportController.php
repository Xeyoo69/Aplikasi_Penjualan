<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'type' => ['required', 'in:sales,products,cashiers'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'type.required' => 'Jenis laporan wajib dipilih.',
            'type.in' => 'Jenis laporan tidak valid.',
            'from.date_format' => 'Format tanggal awal tidak valid.',
            'to.date_format' => 'Format tanggal akhir tidak valid.',
        ]);

        [$from, $to] = $reports->range($validated['from'] ?? null, $validated['to'] ?? null);
        $type = $validated['type'];

        $filename = "laporan-{$type}-{$from}-{$to}.csv";

        return response()->streamDownload(function () use ($reports, $type, $from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            if ($type === 'sales') {
                fputcsv($out, ['Tanggal', 'Jumlah Transaksi', 'Subtotal', 'Diskon', 'Pajak', 'Total']);
                foreach ($reports->salesByDay($from, $to)->cursor() as $row) {
                    fputcsv($out, [
                        $row->sale_date,
                        (int) $row->transactions,
                        (int) $row->subtotal,
                        (int) $row->discount,
                        (int) $row->tax,
                        (int) $row->total,
                    ]);
                }
            } elseif ($type === 'products') {
                fputcsv($out, ['Produk', 'SKU', 'Qty Terjual', 'Total Penjualan']);
                foreach ($reports->topProducts($from, $to)->cursor() as $row) {
                    fputcsv($out, [
                        $row->product_name,
                        $row->sku,
                        (int) $row->qty,
                        (int) $row->total,
                    ]);
                }
            } else {
                fputcsv($out, ['Kasir', 'Jumlah Transaksi', 'Total Penjualan']);
                foreach ($reports->perCashier($from, $to)->cursor() as $row) {
                    fputcsv($out, [
                        $row->name,
                        (int) $row->transactions,
                        (int) $row->total,
                    ]);
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
