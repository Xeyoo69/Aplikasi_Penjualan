<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    /**
     * Membuat nomor invoice unik berurutan per hari: INV-YYYYMMDD-0001.
     *
     * Baris sequence dikunci (lockForUpdate) agar dua transaksi bersamaan
     * tidak mendapat nomor yang sama.
     */
    public function generate(?CarbonInterface $date = null): string
    {
        $day = Carbon::parse($date ?? now())->toDateString();

        return DB::transaction(function () use ($day) {
            $sequence = InvoiceSequence::query()
                ->whereKey($day)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                try {
                    $sequence = InvoiceSequence::query()->create([
                        'date' => $day,
                        'last_number' => 0,
                    ]);
                } catch (QueryException) {
                    // Proses lain membuat baris yang sama lebih dulu.
                    $sequence = InvoiceSequence::query()
                        ->whereKey($day)
                        ->lockForUpdate()
                        ->firstOrFail();
                }
            }

            $sequence->last_number = $sequence->last_number + 1;
            $sequence->save();

            return sprintf(
                'INV-%s-%04d',
                Carbon::parse($day)->format('Ymd'),
                $sequence->last_number
            );
        }, 3);
    }
}
